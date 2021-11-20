<?php

namespace Tests;

use App\CronParser;
use App\NotStandardFormatException;
use App\ValueNotInRangeException;
use PHPUnit\Framework\TestCase;

class CronParserTest extends TestCase
{
    public function testAnyValue(): void
    {
        $cronParser = new CronParser("* * * * * testCommand");

        $this->assertSame(
            "0 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36 37 38 39 40 41 42 43 44 45 46 47 48 49 50 51 52 53 54 55 56 57 58 59",
            $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE)
        );

        $this->assertSame(
            "0 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23",
            $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR)
        );

        $this->assertSame(
            "1 2 3 4 5 6 7",
            $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK)
        );

        $this->assertSame(
            "1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31",
            $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH)
        );

        $this->assertSame(
            "1 2 3 4 5 6 7 8 9 10 11 12",
            $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS)
        );
    }

    public function testNumeric(): void
    {
        $cronParser = new CronParser("0 2 3 4 5 testCommand");
        $this->assertSame("0", $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE));
        $this->assertSame("2", $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR));
        $this->assertSame("3", $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH));
        $this->assertSame("4", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));
        $this->assertSame("5", $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK));

        $cronParser = new CronParser("11 22 30 11 5 testCommand");
        $this->assertSame("11", $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE));
        $this->assertSame("22", $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR));
        $this->assertSame("30", $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH));
        $this->assertSame("11", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));
        $this->assertSame("5", $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK));
    }

    public function testNumericMinuteFailsOutOfRange(): void
    {
        $this->expectException(ValueNotInRangeException::class);
        $cronParser = new CronParser("60 25 0 13 8 testCommand");
        $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE);
    }

    public function testNumericHourFailsOutOfRange(): void
    {
        $this->expectException(ValueNotInRangeException::class);
        $cronParser = new CronParser("60 25 32 13 8 testCommand");
        $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR);
    }

    public function testNumericDayOfMonthFailsOutOfRange(): void
    {
        $this->expectException(ValueNotInRangeException::class);
        $cronParser = new CronParser("60 25 32 13 8 testCommand");
        $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH);
    }

    public function testNumericMonthFailsOutOfRange(): void
    {
        $this->expectException(ValueNotInRangeException::class);
        $cronParser = new CronParser("60 25 32 13 8 testCommand");
        $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS);
    }

    public function testNumericDayOfWeekFailsOutOfRange(): void
    {
        $this->expectException(ValueNotInRangeException::class);
        $cronParser = new CronParser("60 25 32 13 8 testCommand");
        $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK)();
    }

    public function testList(): void
    {
        $cronParser = new CronParser("1,2,3 1,2,3 1,2,3 1,2,3 1,2,3 testCommand");
        $this->assertSame("1 2 3", $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE));
        $this->assertSame("1 2 3", $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR));
        $this->assertSame("1 2 3", $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH));
        $this->assertSame("1 2 3", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));
        $this->assertSame("1 2 3", $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK));
    }

    public function testListMinuteOutOfRange(): void
    {
        $cronParser = new CronParser("5,6,7,80 25 32 13 8 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE);
    }

    public function testListHourOutOfRange(): void
    {
        $cronParser = new CronParser("5 1,2,25 32 13 8 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR);
    }

    public function testListDayOfMonthOutOfRange(): void
    {
        $cronParser = new CronParser("5 1 12,22,23,32 13 8 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH);
    }

    public function testListMonthOutOfRange(): void
    {
        $cronParser = new CronParser("5 1 12 1,2,3,13 8 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS);
    }

    public function testListDayOfWeekOutOfRange(): void
    {
        $cronParser = new CronParser("5 1 12 1 1,2,3,8 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK)();
    }

    public function testRange(): void
    {
        $cronParser = new CronParser("*/15 */4 */5 */7 */3 testCommand");
        $this->assertSame("0 15 30 45", $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE));
        $this->assertSame("0 4 8 12 16 20", $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR));
        $this->assertSame("5 10 15 20 25 30", $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH));
        $this->assertSame("7", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));
        $this->assertSame("3 6", $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK));
    }

    public function testRangeMinuteOutOfRange(): void
    {
        $cronParser = new CronParser("*/65 */25 */35 */15 */9 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE);
    }

    public function testRangeHourOutOfRange(): void
    {
        $cronParser = new CronParser("*/65 */25 */35 */15 */9 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR);
    }

    public function testRangeDayOfMonthOutOfRange(): void
    {
        $cronParser = new CronParser("*/65 */25 */35 */15 */9 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH);
    }

    public function testRangeMonthOutOfRange(): void
    {
        $cronParser = new CronParser("*/65 */25 */35 */15 */9 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS);
    }

    public function testRangeDayOfWeekOutOfRange(): void
    {
        $cronParser = new CronParser("*/65 */25 */35 */15 */9 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK);
    }

    public function testRangeAndStep(): void
    {
        $cronParser = new CronParser("1-10/2 1-10/4 1-20/5 1-8/7 1-5/2 testCommand");
        $this->assertSame("2 4 6 8 10", $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE));
        $this->assertSame("4 8", $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR));
        $this->assertSame("5 10 15 20", $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH));
        $this->assertSame("7", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));
        $this->assertSame("2 4", $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK));
    }

    public function testRangeAndStepWrongFormatMinute(): void
    {
        $cronParser = new CronParser("a-10/2 b-b/4 c-d/5 d-f/7 e-^/2 testCommand");
        $this->expectException(NotStandardFormatException::class);
        $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE);
    }

    public function testRangeAndStepWrongFormatHour(): void
    {
        $cronParser = new CronParser("a-10/2 b-b/4 c-d/5 d-f/7 e-^/2 testCommand");
        $this->expectException(NotStandardFormatException::class);
        $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR);
    }

    public function testRangeAndStepWrongFormatDayOfMonth(): void
    {
        $cronParser = new CronParser("a-10/2 b-b/4 c-d/5 d-f/7 e-^/2 testCommand");
        $this->expectException(NotStandardFormatException::class);
        $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH);
    }

    public function testRangeAndStepWrongFormatMonth(): void
    {
        $cronParser = new CronParser("a-10/2 b-b/4 c-d/5 d-f/7 e-^/2 testCommand");
        $this->expectException(NotStandardFormatException::class);
        $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS);
    }

    public function testRangeAndStepWrongFormatDayOfWeek(): void
    {
        $cronParser = new CronParser("a-10/2 b-b/4 c-d/5 d-f/7 e-^/2 testCommand");
        $this->expectException(NotStandardFormatException::class);
        $cronParser->parseItem($cronParser->cronDaysOfWeek, CronParser::DAYS_OF_WEEK);
    }

    public function testRangeAndStepMinuteOutOfRange(): void
    {
        $cronParser = new CronParser("0-100/2 0-100/2 0-100/2 0-100/2 0-100/2 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronMinutes, CronParser::MINUTE);
    }

    public function testRangeAndStepHourOutOfRange(): void
    {
        $cronParser = new CronParser("0-100/2 0-100/2 0-100/2 0-100/2 0-100/2 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronHours, CronParser::HOUR);
    }

    public function testRangeAndStepDayOfMonthOutOfRange(): void
    {
        $cronParser = new CronParser("0-100/2 0-100/2 0-100/2 0-100/2 0-100/2 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronDaysOfMonth, CronParser::DAYS_OF_MONTH);
    }

    public function testRangeAndStepMonthOutOfRange(): void
    {
        $cronParser = new CronParser("0-100/2 0-100/2 0-100/2 0-100/2 0-100/2 testCommand");
        $this->expectException(ValueNotInRangeException::class);
        $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS);
    }

    public function testMonths(): void
    {
        $cronParser = new CronParser("* * * jan * testCommand");
        $this->assertSame("1", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * feb * testCommand");
        $this->assertSame("2", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * mar * testCommand");
        $this->assertSame("3", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * apr * testCommand");
        $this->assertSame("4", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * may * testCommand");
        $this->assertSame("5", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * jun * testCommand");
        $this->assertSame("6", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * jul * testCommand");
        $this->assertSame("7", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * aug * testCommand");
        $this->assertSame("8", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * sep * testCommand");
        $this->assertSame("9", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * oct * testCommand");
        $this->assertSame("10", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * nov * testCommand");
        $this->assertSame("11", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));

        $cronParser = new CronParser("* * * dec * testCommand");
        $this->assertSame("12", $cronParser->parseItem($cronParser->cronMonths, CronParser::MONTHS));
    }
}