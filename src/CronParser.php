<?php
namespace App;

class CronParser
{
    const FIELD_NAME_LENGTH = 14;
    const COMMAND = 'Command';

    const MINUTE = 'Minute';
    const HOUR = 'Hour';
    const DAYS_OF_MONTH = 'Days of Month';
    const MONTHS = 'Months';
    const DAYS_OF_WEEK = 'Days of Week';
    
    const JAN = 'jan';
    const FEB = 'feb';
    const MAR = 'mar';
    const APR = 'apr';
    const MAY = 'may';
    const JUN = 'jun';
    const JUL = 'jul';
    const AUG = 'aug';
    const SEP = 'sep';
    const OCT = 'oct';
    const NOV = 'nov';
    const DEC = 'dec';

    const MONTH_NAMES = [
        self::JAN,
        self::FEB,
        self::MAR,
        self::APR,
        self::MAY,
        self::JUN,
        self::JUL,
        self::AUG,
        self::SEP,
        self::OCT,
        self::NOV,
        self::DEC
    ];

    const MONTH_VALUES = [
        self::JAN => 1,
        self::FEB => 2,
        self::MAR => 3,
        self::APR => 4,
        self::MAY => 5,
        self::JUN => 6,
        self::JUL => 7,
        self::AUG => 8,
        self::SEP => 9,
        self::OCT => 10,
        self::NOV => 11,
        self::DEC => 12
    ];

    public string $cronMinutes;
    public string $cronHours;
    public string $cronDaysOfMonth;
    public string $cronMonths;
    public string $cronDaysOfWeek;
    public string $cronCommand;
    private array $ranges;

    public function __construct(string $cronString)
    {
        $cronArray = explode(' ', $cronString);
        $this->cronMinutes = $cronArray[0];
        $this->cronHours = $cronArray[1];
        $this->cronDaysOfMonth = $cronArray[2];
        $this->cronMonths = $cronArray[3];
        $this->cronDaysOfWeek = $cronArray[4];
        $this->cronCommand = $cronArray[5];

        $this->ranges = [
            self::MINUTE => range(0, 59),
            self::HOUR => range(0, 23),
            self::DAYS_OF_MONTH => range(1, 31),
            self::MONTHS => range(1, 12),
            self::DAYS_OF_WEEK => range(1, 7),
        ];
    }

    public function parseCronString()
    {
        $this->renderTable([
            self::MINUTE => $this->parseItem($this->cronMinutes, self::MINUTE),
            self::HOUR => $this->parseItem($this->cronHours, self::HOUR),
            self::DAYS_OF_MONTH => $this->parseItem($this->cronDaysOfMonth, self::DAYS_OF_MONTH),
            self::MONTHS => $this->parseItem($this->cronMonths, self::MONTHS),
            self::DAYS_OF_WEEK => $this->parseItem($this->cronDaysOfWeek, self::DAYS_OF_WEEK),
            self::COMMAND => $this->cronCommand,
        ]);
    }

    public function parseItem(string $item, string $type): string
    {
        if ($item === "*") {
            return $this->getAll($type);
        }

        if (is_numeric($item)) {
            return $this->getNumeric($item, $type);
        }

        if (strpos($item, '/') && strpos($item, '-')) {
            return $this->getStepValuesWithRanges($item, $type);
        }

        if (strpos($item, '/')) {
            return $this->getStepValues($item, $type);
        }

        if (strpos($item, '-')) {
            return $this->getRangeValues($item, $type);
        }

        if (strpos($item, ',')) {
            return $this->getListValues($item, $type);
        }

        //Specific to Month:
        if ($type === self::MONTHS && in_array(strtolower($item), self::MONTH_NAMES)) {
            return self::MONTH_VALUES[$item];
        }

        throw new NotStandardFormatException($type . ' is not in standard format');
    }

    private function getAll(string $type): string
    {
        return implode(' ', $this->ranges[$type]);
    }

    private function getNumeric(int $item, string $type): string
    {
        if (!in_array($item, $this->ranges[$type])) {
            throw new ValueNotInRangeException($type . ' value not in range');
        }

        return $item;
    }

    private function getListValues(string $item, string $type): string
    {
        $list = explode(',', $item);

        $values = [];
        foreach ($list as $value) {
            if (!in_array($value, $this->ranges[$type])) {
                throw new ValueNotInRangeException($type . ' value not in range');
            }

            $values[] = $value;
        }

        return $this->renderValues($values);
    }

    private function getStepValues(string $item, string $type, int $startRange = null, int $endRange = null): string
    {
        $itemArray = explode('/', $item);
        $left = $itemArray[0];
        $right = $itemArray[1];

        $customRanges = false;

        if (!$startRange) {
            $customRanges = true;
            $startRange = min($this->ranges[$type]);
        }

        if (!$endRange) {
            $customRanges = true;
            $endRange = max($this->ranges[$type]);
        }

        if ($left !== "*" && preg_match('/[0-9]+-[0-9]+/', $left) !== 1) {
            throw new NotStandardFormatException($type . ' is not in standard format');
        }

        if ($right < $startRange || $right > $endRange) {
            throw new ValueNotInRangeException($type . ' value not in range');
        }

        if ($customRanges) {
            if ($startRange < min($this->ranges[$type]) || $endRange > max($this->ranges[$type])) {
                throw new ValueNotInRangeException($type . ' value not in range');
            }
        }

        //Increment from range start to end, by interval $right
        for ($i = $startRange; $i <= $endRange; $i++) {
            if ($i % $right === 0) {
                $values[] = $i;
            }
        }

        return $this->renderValues($values);
    }

    private function getStepValuesWithRanges(string $item, string $type): string
    {
        $itemToArray = explode('/', $item);

        $ranges = explode('-', $itemToArray[0]);

        $startRange = (int) $ranges[0];
        $endRange = (int) $ranges[1];

        return $this->getStepValues($item, $type, $startRange, $endRange);
    }

    private function getRangeValues(string $item, string $type): string
    {
        $itemArray = explode('-', $item);
        $left = $itemArray[0];
        $right = $itemArray[1];

        $startRange = min($this->ranges[$type]);
        $endRange = max($this->ranges[$type]);

        if ($left < $startRange || $right > $endRange) {
            throw new ValueNotInRangeException($type . ' value not in range');
        }

        $values = [];

        for ($i = $left; $i <= $right; $i++) {
            $values[] = $i;
        }

        return $this->renderValues($values);
    }


    private function renderValues(array $values): string
    {
        $returnString = '';

        $listLength = count($values);

        foreach ($values as $key => $value) {
            $returnString .= $value;
            if ($key < $listLength - 1) {
                $returnString .= ' ';
            }
        }

        return $returnString;
    }

    private function renderTable(array $data): void
    {
        foreach ($data as $key => $value) {
            $numberOfSpaces = self::FIELD_NAME_LENGTH - count(str_split($key));

            echo $key;

            for ($index = 0; $index < $numberOfSpaces; $index++) {
                echo ' ';
            }

            //Render values
            echo $value . "\n";
        }
    }
}