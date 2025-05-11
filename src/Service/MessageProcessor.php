<?php
namespace App\Service;

use App\Model\Message;
use App\Model\Review;
use App\Model\Incident;
use App\Model\Failure;
use Psr\Log\LoggerInterface;

class MessageProcessor
{
    private LoggerInterface $logger;
    private array $processedDescriptions = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $inputFile JSON source file path
     * @return array<string,mixed>
     */
    public function process(string $inputFile): array
    {
        $this->logger->info("Starting message processing");
        $data = json_decode(file_get_contents($inputFile), true, 512, JSON_THROW_ON_ERROR);
        $total = count($data);
        $reviews = [];
        $incidents = [];
        $failures = [];

        foreach ($data as $item) {
            $message = new Message($item);
            $desc = $message->getDescription();
            if (in_array($desc, $this->processedDescriptions, true)) {
                $this->logger->info("Duplicate skipped: $desc");
                continue;
            }
            $this->processedDescriptions[] = $desc;
            try {
                if (stripos($desc, 'przegląd') !== false) {
                    $reviews[] = $this->processReview($message);
                    $this->logger->info('Processing processing maintenance for: ' . $message->getDescription());

                } else {
                    $incidents[] = $this->processIncident($message);
                    $this->logger->info('Processing malfunction for: ' . $message->getDescription());
                }
            } catch (\Throwable $e) {
                $this->logger->error("Processing error: {$e->getMessage()}");
                $failures[] = new Failure($item);
            }
        }

        return compact('total', 'reviews', 'incidents', 'failures');
    }

    private function processReview(Message $message): Review
    {
        $review = new Review();
        $review->description = $message->getDescription();
        $due = $message->getDueDate();
        if ($due) {
            $review->reviewDate = $due->format('Y-m-d');
            $review->week = (int)$due->format('W');
            $review->status = 'zaplanowano';
        } else {
            $review->reviewDate = '';
            $review->week = 0;
            $review->status = 'nowy';
        }
        $review->recommendations = '';
        $review->phone = $message->getPhone();
        $review->createdAt = (new \DateTimeImmutable())->format('Y-m-d');
        $this->logger->info("Maintenance created: {$review->description}");
        return $review;
    }

    private function processIncident(Message $message): Incident
    {
        $incident = new Incident();
        $incident->description = $message->getDescription();
        $text = mb_strtolower($incident->description, 'UTF-8');
        if (str_contains($text, 'bardzo pilne')) {
            $incident->priority = 'krytyczny';
        } elseif (str_contains($text, 'pilne')) {
            $incident->priority = 'wysoki';
        } else {
            $incident->priority = 'normalny';
        }
        $due = $message->getDueDate();
        if ($due) {
            $incident->serviceDate = $due->format('Y-m-d');
            $incident->status = 'termin';
        } else {
            $incident->serviceDate = '';
            $incident->status = 'nowy';
        }
        $incident->serviceNotes = '';
        $incident->phone = $message->getPhone();
        $incident->createdAt = (new \DateTimeImmutable())->format('Y-m-d');
        $this->logger->info("Malfunction created: {$incident->description}");
        return $incident;
    }
}