<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Service\MessageProcessor;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

class MessageProcessorTest extends TestCase
{
    private MessageProcessor $processor;
    private string $tmpFile;

    protected function setUp(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $this->processor = new MessageProcessor($logger);
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'msg');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testProcessReview(): void
    {
        $input = [[
            'description' => 'Regular przegląd systemu',
            'dueDate' => '2025-06-15',
            'phone' => '123'
        ]];
        file_put_contents($this->tmpFile, json_encode($input));

        $res = $this->processor->process($this->tmpFile);

        $this->assertCount(1, $res['reviews']);
        $review = $res['reviews'][0];
        $this->assertEquals('przegląd', $review->type);
        $this->assertEquals('2025-06-15', $review->reviewDate);
        $this->assertEquals(24, $review->week);
        $this->assertEquals('zaplanowano', $review->status);
        $this->assertEquals('123', $review->phone);
    }

    public function testProcessIncidentPriority(): void
    {
        $input = [[
            'description' => 'Awaria - bardzo pilne',
            'phone' => '987'
        ]];
        file_put_contents($this->tmpFile, json_encode($input));

        $res = $this->processor->process($this->tmpFile);

        $this->assertCount(1, $res['incidents']);
        $incident = $res['incidents'][0];
        $this->assertEquals('zgłoszenie awarii', $incident->type);
        $this->assertEquals('krytyczny', $incident->priority);
        $this->assertEquals('nowy', $incident->status);
        $this->assertEquals('987', $incident->phone);
    }

    public function testDuplicateDescription(): void
    {
        $input = [
            ['description' => 'Duplikat testowy', 'phone' => '111'],
            ['description' => 'Duplikat testowy', 'phone' => '222']
        ];
        file_put_contents($this->tmpFile, json_encode($input));

        $res = $this->processor->process($this->tmpFile);

        $this->assertCount(1, $res['incidents']);
        $this->assertEquals('Duplikat testowy', $res['incidents'][0]->description);
    }

    public function testFailureOnBadDate(): void
    {
        $input = [[
            'description' => 'Awaria z błędną datą',
            'dueDate' => '2020-99-99',
            'phone' => '000'
        ]];
        file_put_contents($this->tmpFile, json_encode($input));

        $res = $this->processor->process($this->tmpFile);

        $this->assertCount(0, $res['incidents']);
        $this->assertCount(1, $res['failures']);
    }
    public function testSpecificReview17(): void
    {
        $input = [[
            'number' => 17,
            'description' => 'Mam na panów złą wiadomość. Zapraszam na ponowny przegląd maty zabezpieczającej w meblu kasowym. Ostatnio był a sprzęt niestety nie działa, pilne!',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        ]];
        file_put_contents($this->tmpFile, json_encode($input));

        $res = $this->processor->process($this->tmpFile);

        $this->assertCount(1, $res['reviews']);
        $review = $res['reviews'][0];
        $this->assertEquals('przegląd', $review->type);
        $this->assertEquals('2020-03-02', $review->reviewDate);
        $this->assertEquals(10, $review->week);
        $this->assertEquals('zaplanowano', $review->status);
        $this->assertEquals('+48505167301', $review->phone);
    }
}
