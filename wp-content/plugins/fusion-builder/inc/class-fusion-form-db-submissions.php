<?php
/**
 * Form Submission Handler.
 *
 * @package fusion-builder
 * @since 3.1
 */

/**
 * Form Submission.
 *
 * @since 3.1
 */
class Fusion_Form_DB_Submissions extends Fusion_Form_DB_Items {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 3.1
	 * @var string
	 */
	protected $table_name = 'fusion_form_submissions';

	/**
	 * Delete a submission.
	 *
	 * @access public
	 * @since 3.1
	 * @param int|array $ids       The submission ID(s).
	 * @param string    $id_column The column to use in our WHERE query fragment.
	 * @return void
	 */
	public function delete( $ids, $id_column = 'id' ) {

		$ids = (array) $ids;

		foreach ( $ids as $id ) {

			// Get the form-ID for this submission.
			$submission = $this->get( [ 'where' => [ 'id' => (int) $id ] ] );
			if ( isset( $submission[0] ) ) {

				// Get the form-ID.
				$form_id = $submission[0]->form_id;

				// Decrease submissions count.
				$forms = new Fusion_Form_DB_Forms();
				$forms->decrease_submissions_count( $form_id );
			}

			// Delete submission.
			parent::delete( $id );

			// Delete submission entries.
			$entries = new Fusion_Form_DB_Entries();
			$entries->delete( $id, 'submission_id' );
		}
	}

	/**
	 * Get the form database entries.
	 *
	 * @param int $form_id The form id.
	 * @return int
	 */
	public function count_form_database_entries( $form_id ) {
		global $wpdb;

		$forms    = new Fusion_Form_DB_Forms();
		$db       = new Fusion_Form_DB();
		$table_id = $forms->get_form_table_id( $form_id );

		$submission_count = (int) $db->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}{$this->table_name}` WHERE `{$wpdb->prefix}{$this->table_name}`.`form_id` = %d", $table_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $submission_count;
	}
}
