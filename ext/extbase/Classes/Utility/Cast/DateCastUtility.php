<?php

namespace Lpc\LpcPrayer\Utility\Cast;

/**
 * Class DateCastUtility
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage
 * @package Lpc\LpcPrayer\Utility\Cast
 */
class DateCastUtility {

	public static $formatDate = 'd.m.y';
	public static $formatDatetime = 'd.m.Y H:i:s';

	/**
	 * Convert a int (timestamp), string (strtotime) to a dateobject
	 * @param int|string|dateobject $date
	 * @return \DateTime|null
	 */
	public static function getDateObject($date) {
		// not a datetime object
		if (!is_object($date) || !(get_class($date) == 'DateTime')) {
			// is timestamp, cast
			if (intval($date) > 0 && $date == intval($date)) {
				$timestamp = $date;
			} else if (is_string($date) && strlen($date) > 0 && $date !== '0000-00-00') {
				// if string cast to datetime object
				$timestamp = strtotime($date);
			}

			if ($timestamp) {
				$date = new \DateTime();
				$date->setTimestamp($timestamp);
			}
		}

		if (!is_object($date) || !(get_class($date) == 'DateTime')) {
			// invalid date: set to null
			$date = null;
		}
		return $date;
	}

	/**
	 * Gets the start and end date by a week number (with custom rules)
	 * @param int $week the week number (kw)
	 * @param int $dayOffset offset of days (1 = monday, 0/7 = sunday)
	 * @param int $year optional year (default is current year)
	 * @param bool|false $returnObject true if the date objects should be returned
	 * @param int $hour
	 * @param int $min
	 * @param int $sec
	 * @return array|string
	 */
	public static function getDateByWeekNumber($week, $dayOffset = 1, $year = 0, $returnObject = false, $hour = 0, $min = 0, $sec = 0) {
		$return = array();

		$week = intval($week);
		if ($week == 0) {
			return '';
		}

		$year = intval($year);
		if ($year == 0) {
			$year = date("Y");
		}

		$weekStart = new \DateTime();
		$weekStart->setISODate($year, $week, $dayOffset);
		$weekStart->setTime($hour, $min, $sec);
		$weekEnd = clone $weekStart;
		$weekEnd = $weekEnd->add(new \DateInterval('P7D'));

		$return['start'] = $weekStart->format(self::$formatDate);
		$return['end'] = $weekEnd->format(self::$formatDate);
		// $return['value'] = $return['start'] . ' - ' . $return['end'];
		$return['value'] = self::shortTimeRange($weekStart, $weekEnd);

		if ($returnObject) {
			$return['object'] = array();
			$return['object']['start'] = $weekStart;
			$return['object']['end'] = $weekEnd;
			return $return;
		} else {
			return $return['value'];
		}

	}

	/**
	 * Gets the calendar week by a date with custom rules
	 * @param null|\DateTime $date default is now
	 * @param int $dayOffset which day the week should start (1 = monday, 0/7 = sunday)
	 * @param int $hour the hour, the new week should start
	 * @param int $min the min, the new week should start
	 * @param int $sec the sec, the new week should start
	 * @return int week the week number based on the configuration
	 */
	public static function getWeekNumberByDate($date = null, $dayOffset = 1, $hour = 0, $min = 0, $sec = 0) {
		if (!$date instanceof \DateTime) {
			$date = new \DateTime();
		}
		// fix offset different to setIsoDate -> 0 is weekchange on sunday
		$date->add(new \DateInterval('P1D'));

		// sub the offset
		$date->sub(new \DateInterval('P'.$dayOffset.'DT'.$hour.'H'.$min.'M'.$sec.'S'));
		$week = $date->format('W');
		$year = $date->format('Y');

		return array(
			'week' => $week,
			'year' => $year
		);
	}

	/**
	 * Short a from to date -> remove all obsolete (duplicate) information from first date
	 * @param \DateTime $dateFrom
	 * @param \DateTime $dateTo
	 * @return string
	 */
	public function shortTimeRange($dateFrom, $dateTo) {
		$timeRange = $dateFrom->format('d').'.';
		// if ($dateFrom->format('m') !== $dateTo->format('m')) {
			$timeRange .= $dateFrom->format('m') . '.';
		// }
		if ($dateFrom->format('Y') !== $dateTo->format('Y')) {
			$timeRange .= $dateFrom->format('Y');
		}
		$timeRange .= ' - ' . $dateTo->format('d.m.Y');
		return $timeRange;
	}

}
