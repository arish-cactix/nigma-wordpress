<?php // phpcs:ignore
/**
 * Snapshot models: Schedules model
 *
 * Holds information for backups schedules.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Task;
use DateTime;
use DateTimeZone;

/**
 * Backup schedules model class
 */
class Schedule extends Model {

	/**
	 * Constructor
	 *
	 * @param array $data Contains frequency, status, time, files, DB tables for the scheduled backup.
	 *
	 * Sets up data.
	 */
	public function __construct( $data ) {
		$this->populate( $data );
	}

	/**
	 * Initializes the data
	 *
	 * @param array $data Contains frequency, status, time, files, DB tables for the scheduled backup.
	 */
	public function populate( $data ) {
		$this->set_data( $data );
	}

	/**
	 * Returns human-readable schedule details
	 *
	 * @param bool $no_cache true if don't need to cache in static var.
	 * @param bool $return_empty returns empty schedule without request if true.
	 * @return array
	 */
	public static function get_schedule_info( $no_cache = false, $return_empty = false ) {
		if ( $return_empty ) {
			$active_schedule = null;
		} else {
			$active_schedule = Task\Request\Schedule::get_current_schedule( $no_cache );
			if ( is_wp_error( $active_schedule ) ) {
				wp_send_json_error( $active_schedule );
			}
		}

		$schedule_is_active = true;

		// Only if there's no valid schedule_id, do we create new schedule, in all other cases (even when stored schedule is inactive, we update).
		if ( isset( $active_schedule['schedule_id'] ) && $active_schedule['schedule_id'] ) {
			$schedule_action = 'update';
		} else {
			$schedule_action = 'create';
		}

		if ( ! isset( $active_schedule['bu_status'] ) || 'inactive' === $active_schedule['bu_status'] ) {
			$active_schedule    = null;
			$schedule_is_active = false;
		}

		$frequency    = isset( $active_schedule['bu_frequency'] ) ? $active_schedule['bu_frequency'] : null;
		$time_utc     = isset( $active_schedule['bu_time'] ) ? $active_schedule['bu_time'] : null;
		$weekday_utc  = isset( $active_schedule['bu_frequency_weekday'] ) ? intval( $active_schedule['bu_frequency_weekday'] ) : null;
		$monthday_utc = isset( $active_schedule['bu_frequency_monthday'] ) ? intval( $active_schedule['bu_frequency_monthday'] ) : null;

		$converted = self::convert_timezone( $frequency, new \DateTimeZone( 'UTC' ), wp_timezone(), $time_utc, $weekday_utc, $monthday_utc );
		$time      = $converted['time'];
		$weekday   = $converted['weekday'];
		$monthday  = $converted['monthday'];

		$result = '';

		switch ( $frequency ) {
			case 'daily':
				$frequency_human = __( 'Daily', 'snapshot' );
				break;
			case 'weekly':
				$frequency_human = __( 'Weekly', 'snapshot' );
				break;
			case 'monthly':
				$frequency_human = __( 'Monthly', 'snapshot' );
				break;
			default:
				$frequency_human = __( 'None', 'snapshot' );
				$result          = __( 'None', 'snapshot' );
				break;
		}

		if ( $time ) {
			$hour_list = Helper\Datetime::get_hour_list();
			$result    = $frequency_human;
			if ( 'monthly' === $frequency && $monthday ) {
				$result .= '/' . $monthday;
			}
			$result .= ' @ ' . ( isset( $hour_list[ $time ] ) ? $hour_list[ $time ] : $time );
		}

		if ( 'weekly' === $frequency ) {
			switch ( $weekday ) {
				case 1:
					$result .= ' ' . __( 'on Sunday', 'snapshot' );
					break;
				case 2:
					$result .= ' ' . __( 'on Monday', 'snapshot' );
					break;
				case 3:
					$result .= ' ' . __( 'on Tuesday', 'snapshot' );
					break;
				case 4:
					$result .= ' ' . __( 'on Wednesday', 'snapshot' );
					break;
				case 5:
					$result .= ' ' . __( 'on Thursday', 'snapshot' );
					break;
				case 6:
					$result .= ' ' . __( 'on Friday', 'snapshot' );
					break;
				case 7:
					$result .= ' ' . __( 'on Saturday', 'snapshot' );
					break;
				default:
					break;
			}//end switch
		}//end if

		$next_backup_timestamp = self::get_next_backup_timestamp( $frequency, $time_utc, $weekday_utc, $monthday_utc );
		if ( $next_backup_timestamp ) {
			$dt = new \DateTime();
			$dt->setTimezone( wp_timezone() );
			$dt->setTimestamp( $next_backup_timestamp );
			$next_backup_time = $dt->format( Helper\Datetime::get_format() );
		} else {
			$next_backup_time = __( 'Never', 'snapshot' );
		}

		$result = array(
			'text'               => $result,
			'frequency_human'    => $frequency_human,
			'next_backup_time'   => $next_backup_time,
			'values'             => array(
				'frequency'          => $frequency,
				'time'               => $time,
				'frequency_weekday'  => $weekday,
				'frequency_monthday' => $monthday,
			),
			'schedule_action'    => $schedule_action,
			'schedule_is_active' => $schedule_is_active,
		);

		return $result;
	}

	/**
	 * Returns next expected backup time
	 *
	 * @param string     $frequency Frequency.
	 * @param int|string $time      Time in timestamp | "H:i" format.
	 * @param int|null   $weekday   Day of week (1 - Sunday, ..., 7 - Saturday).
	 * @param int|null   $monthday  Day of month.
	 * @return int|null Timestamp of next backup
	 */
	public static function get_next_backup_timestamp( $frequency, $time, $weekday = null, $monthday = null ) {
		$timezone = new \DateTimeZone( 'UTC' );
		$now      = new \DateTime();
		$now->setTimezone( $timezone );
		$weekday  = intval( $weekday );
		$monthday = intval( $monthday );

		$result = null;

		switch ( $frequency ) {
			case 'daily':
				$dt = DateTime::createFromFormat( 'Y-m-d H:i:s', $now->format( 'Y-m-d' ) . " $time:00", $timezone );
				if ( $dt->getTimestamp() - $now->getTimestamp() > 0 ) {
					// Today.
					$result = $dt->getTimestamp();
				} else {
					// Tomorrow.
					$tomorrow = clone $now;
					$tomorrow->setTime( 12, 0, 0 );
					$tomorrow->setTimestamp( $tomorrow->getTimestamp() + 86400 );
					$dt     = DateTime::createFromFormat( 'Y-m-d H:i:s', $tomorrow->format( 'Y-m-d' ) . " $time:00", $timezone );
					$result = $dt->getTimestamp();
				}
				break;
			case 'weekly':
				$now_weekday = intval( $now->format( 'w' ) ) + 1;
				$add_days    = $weekday - $now_weekday;
				$dt          = DateTime::createFromFormat( 'Y-m-d H:i:s', $now->format( 'Y-m-d' ) . " $time:00", $timezone );
				if ( 0 === $add_days && $dt->getTimestamp() - $now->getTimestamp() > 0 ) {
					// Today.
					$result = $dt->getTimestamp();
				} else {
					// Next week.
					if ( $add_days <= 0 ) {
						$add_days += 7;
					}
					$next_date = clone $now;
					$next_date->setTime( 12, 0, 0 );
					$next_date->setTimestamp( $next_date->getTimestamp() + 86400 * $add_days );
					$dt     = DateTime::createFromFormat( 'Y-m-d H:i:s', $next_date->format( 'Y-m-d' ) . " $time:00", $timezone );
					$result = $dt->getTimestamp();
				}
				break;
			case 'monthly':
				$now_monthday = intval( $now->format( 'j' ) );
				$dt           = DateTime::createFromFormat( 'Y-m-d H:i:s', $now->format( 'Y-m-d' ) . " $time:00", $timezone );
				if ( $now_monthday === $monthday && $dt->getTimestamp() - $now->getTimestamp() > 0 ) {
					// Today.
					$result = $dt->getTimestamp();
				} elseif ( $monthday - $now_monthday > 0 ) {
					// This month.
					$dt     = DateTime::createFromFormat( 'Y-m-j H:i:s', $now->format( 'Y-m' ) . "-$monthday $time:00", $timezone );
					$result = $dt->getTimestamp();
				} else {
					// Next month.
					$year  = intval( $now->format( 'Y' ) );
					$month = intval( $now->format( 'n' ) );
					++$month;
					if ( $month > 12 ) {
						$month = 1;
						++$year;
					}
					$next_date = clone $now;
					$next_date->setTime( 12, 0, 0 );
					$next_date->setDate( $year, $month, $monthday );
					$dt     = DateTime::createFromFormat( 'Y-m-d H:i:s', $next_date->format( 'Y-m-d' ) . " $time:00", $timezone );
					$result = $dt->getTimestamp();
				}//end if
				break;
		}//end switch

		return $result;
	}

	/**
	 * Convert timezone
	 *
	 * @param string       $frequency Frequency.
	 * @param DateTimeZone $from_tz   Convert from timezone.
	 * @param DateTimeZone $to_tz     Convert to timezone.
	 * @param string       $time      Time in "H:i" format.
	 * @param int|null     $weekday   Day of week (1 - Sunday, ..., 7 - Saturday).
	 * @param int|null     $monthday  Day of month.
	 * @return array
	 */
	public static function convert_timezone( $frequency, DateTimeZone $from_tz, DateTimeZone $to_tz, $time, $weekday = null, $monthday = null ) {
		$next_backup_ts = self::get_next_backup_timestamp( $frequency, $time, $weekday, $monthday );
		$weekday        = intval( $weekday );
		$monthday       = intval( $monthday );

		$datetime = new \DateTime();

		if ( $next_backup_ts ) {
			$datetime->setTimestamp( $next_backup_ts );
			$datetime->setTimezone( $from_tz );
		}

		$result = array(
			'frequency' => $frequency,
			'time'      => null,
			'weekday'   => null,
			'monthday'  => null,
		);

		switch ( $frequency ) {
			case 'daily':
				$dt = DateTime::createFromFormat( 'Y-m-d H:i:s', $datetime->format( 'Y-m-d' ) . " $time:00", $from_tz );
				$dt->setTimezone( $to_tz );
				$result['time'] = $dt->format( 'H:i' );
				break;
			case 'weekly':
				$dt = DateTime::createFromFormat( 'Y-m-d H:i:s', $datetime->format( 'Y-m-d' ) . ' 12:00:00', $from_tz );
				$dt->setTimestamp( $dt->getTimestamp() + ( $weekday - $dt->format( 'N' ) ) * 86400 );
				$dt = DateTime::createFromFormat( 'Y-m-d H:i:s', $dt->format( 'Y-m-d' ) . " $time:00", $from_tz );
				$dt->setTimezone( $to_tz );
				$result['time']    = $dt->format( 'H:i' );
				$result['weekday'] = intval( $dt->format( 'N' ) );
				break;
			case 'monthly':
				$dt = DateTime::createFromFormat( 'Y-m-j H:i:s', $datetime->format( 'Y-m' ) . "-$monthday 12:00:00", $from_tz );
				$dt = DateTime::createFromFormat( 'Y-m-d H:i:s', $dt->format( 'Y-m-d' ) . " $time:00", $from_tz );
				$dt->setTimezone( $to_tz );
				$result_monthday = intval( $dt->format( 'j' ) );
				if ( 28 === $monthday && ( $result_monthday < 2 || $result_monthday > 28 ) ) {
					$result_monthday = $monthday;
				} elseif ( 1 === $monthday && $result_monthday > 2 ) {
					$result_monthday = 1;
				}
				$result['time']     = $dt->format( 'H:i' );
				$result['monthday'] = $result_monthday;
				break;
		}//end switch

		return $result;
	}

	/**
	 * When taking the schedule data from the db, their keys need to be transformed to comply with schedule_request()'s naming.
	 *
	 * @param string $request_data Schedule request data.
	 *
	 * @return array
	 */
	public static function transform_data( $request_data ) {
		// @TODO: Refactor this *please*.
		$transformed_data                       = array();
		$transformed_data['schedule_id']        = isset( $request_data['schedule_id'] ) ? $request_data['schedule_id'] : null;
		$transformed_data['site_id']            = isset( $request_data['site_id'] ) ? $request_data['site_id'] : null;
		$transformed_data['frequency']          = isset( $request_data['bu_frequency'] ) ? $request_data['bu_frequency'] : null;
		$transformed_data['status']             = isset( $request_data['bu_status'] ) ? $request_data['bu_status'] : null;
		$transformed_data['files']              = isset( $request_data['bu_files'] ) ? $request_data['bu_files'] : null;
		$transformed_data['tables']             = isset( $request_data['bu_tables'] ) ? $request_data['bu_tables'] : null;
		$transformed_data['time']               = isset( $request_data['bu_time'] ) ? $request_data['bu_time'] : null;
		$transformed_data['frequency_weekday']  = isset( $request_data['bu_frequency_weekday'] ) ? $request_data['bu_frequency_weekday'] : null;
		$transformed_data['frequency_monthday'] = isset( $request_data['bu_frequency_monthday'] ) ? $request_data['bu_frequency_monthday'] : null;

		return $transformed_data;
	}

	/**
	 * Convert WP timezone option into a DateTimeZone instance.
	 *
	 * In order to use the convert_timezone method with timezones coming from the Hub, we need to have a DateTimeZone instance of the timezone used in the Hub.
	 *
	 * Uses the https://developer.wordpress.org/reference/functions/wp_timezone_string/ handling of the WP options.
	 *
	 * @param string $hub_gmt_offset Hub timezone either in timezone_string or gmt_offset.
	 *
	 * @return DateTimeZone
	 */
	public static function convert_to_DateTimeZone( $hub_gmt_offset ) {
		$offset  = (float) $hub_gmt_offset;
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign      = ( $offset < 0 ) ? '-' : '+';
		$abs_hour  = abs( $hours );
		$abs_mins  = abs( $minutes * 60 );
		$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

		return new DateTimeZone( $tz_offset );
	}
}