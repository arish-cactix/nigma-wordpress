<?php
/**
 * Snapshot date helper
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Date class
 */
class Date {

	/**
	 * Check if we're on the black friday sell period.
	 *
	 * @return boolean
	 */
	public static function is_black_friday_month() {
		// We don't want to show it before 1st of November.
		if ( date_create( date_i18n( 'd-m-Y' ) ) < date_create( date_i18n( '01-11-Y' ) ) ) {
			return false;
		}

		// We don't want to show it after 6th of December.
		if ( date_create( date_i18n( 'd-m-Y' ) ) >= date_create( date_i18n( '06-12-Y' ) ) ) {
			// Delete the site 'snapshot-show-black-friday' option key from DB.
			delete_site_option( 'snapshot-show-black-friday' );
			return false;
		}

		return true;
	}

	/**
	 * Get randomized weekday.
	 *
	 * Currently set from Wednesday to Saturday.
	 *
	 * @return integer
	 */
	public static function get_randomized_weekday(): int {
		$weekdays = range( 4, 7 );
		// From Wednesday - 4 To Saturday - 7.
		$random = array_rand( $weekdays );

		return $weekdays[ $random ];
	}
}