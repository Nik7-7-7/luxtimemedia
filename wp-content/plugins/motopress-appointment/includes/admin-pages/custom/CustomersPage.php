<?php

namespace MotoPress\Appointment\AdminPages\Custom;

use MotoPress\Appointment\Entities\Customer;
use MotoPress\Appointment\Handlers\SecurityHandler;
use MotoPress\Appointment\Fields\FieldsFactory;
use MotoPress\Appointment\ListTables\CustomersListTable;

/**
 *
 * @since 1.18.0
 */
class CustomersPage extends AbstractCustomPage {

	const CUSTOMER_SUCCESS_DELETED_NOTICE_TRANSIENT = 'customer_success_deleted_notice';

	/**
	 * @var CustomersListTable
	 */
	protected $table;

	/**
	 * @var string[]|null
	 */
	protected $errorMessages = null;

	protected $successMessage = null;

	/**
	 * @var bool
	 */
	protected $saved = null;

	/**
	 * @var Customer|null
	 */
	protected $customer = null;

	protected function enqueueScripts() {
		mpa_assets()->enqueueScript( 'mpa-customers-page' );
		mpa_assets()->enqueueStyle( 'mpa-admin' );
	}

	/**
	 * In this method, you can add actions to their handlers, which will work only for the current page.
	 * Warning: you can only use hooks that fire later than the current_screen hook.
	 *
	 * @access protected
	 */
	public function load() {
		add_action( 'admin_notices', array( $this, 'showCustomerSuccessDeletedNotice' ) );

		$this->initListTable();
		$this->initPageActions();
	}

	/**
	 * @access protected
	 */
	public function display() {
		echo '<div class="wrap">';
		if ( $this->isActionEdit() ) {
			mpa_display_template( 'private/pages/customer-edit.php',
				[
					'fields'         => $this->getFields(),
					'userId'         => $this->customer->getUserId(),
					'errorMessages'  => $this->errorMessages,
					'successMessage' => $this->successMessage,
				]
			);
		} else {
			?>
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Customers', 'motopress-appointment' ); ?></h1>
            <hr class="wp-header-end"/>
			<?php
			$this->table->prepare_items();
			$this->table->display();
		}
		echo '</div>';
	}

	protected function getPageTitle() {
		return __( 'Customers', 'motopress-appointment' );
	}

	protected function getMenuTitle() {
		return __( 'Customers', 'motopress-appointment' );
	}

	protected function getAllAssociatedUserIdsExcludingSelected( int $selectedUserId ) {
		/**
		 * @var int[]|array $associatedUserIds An array of user_id's which need exclude from the field.
		 */
		$associatedUserIds = mpapp()->repositories()->customer()->findAllAssociatedUserIds();

		// To be able to re-save customer data with the current user_id,
		// remove selected user id from $associatedUserIds
		$selectedUserIdKey = array_search( $selectedUserId, $associatedUserIds );
		if ( false !== $selectedUserIdKey ) {
			unset( $associatedUserIds[ $selectedUserIdKey ] );
		}

		return $associatedUserIds;
	}

	protected function getFields() {
		if ( ! $this->customer ) {
			return [];
		}

		$selectedUserId = $this->customer->getUserId();

		return [
			FieldsFactory::createField( 'user_id', [
				'label'   => __( 'User', 'motopress-appointment' ),
				'type'    => 'user-select',
				'size'    => 'regular',
				'exclude' => $this->getAllAssociatedUserIdsExcludingSelected( $selectedUserId ),
			], $selectedUserId ),

			FieldsFactory::createField( 'name', [
				'label' => __( 'Name', 'motopress-appointment' ),
				'type'  => 'text',
				'size'  => 'regular',
			], $this->customer->getName() ),

			FieldsFactory::createField( 'email', [
				'label' => __( 'Email', 'motopress-appointment' ),
				'type'  => 'email',
				'size'  => 'regular',
			], $this->customer->getEmail() ),

			FieldsFactory::createField( 'phone', [
				'label'                  => __( 'Phone', 'motopress-appointment' ),
				'type'                   => 'phone',
				'size'                   => 'regular',
			], $this->customer->getPhone() ),

			FieldsFactory::createField( 'date_registered', [
				'label'    => __( 'Date Registered', 'motopress-appointment' ),
				'type'     => 'text',
				'readonly' => true,
			], $this->customer->getDateRegistered()->format( 'Y-m-d H:i:s' ) ),

			FieldsFactory::createField( 'last_active', [
				'label'    => __( 'Last Active', 'motopress-appointment' ),
				'type'     => 'text',
				'readonly' => true,
			], $this->customer->getLastActive() ? $this->customer->getLastActive()->format( 'Y-m-d H:i:s' ) : '-' ),
		];
	}

	/**
	 * @access protected
	 */
	public function showCustomerSuccessDeletedNotice() {
		if ( $this->isActionDelete() ) {
			return;
		}

		if ( get_transient( self::CUSTOMER_SUCCESS_DELETED_NOTICE_TRANSIENT ) ) {
			delete_transient( self::CUSTOMER_SUCCESS_DELETED_NOTICE_TRANSIENT );

			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html__( 'The client has been successfully deleted.', 'motopress-appointment' ) );
		}
	}

	protected function initListTable() {
		$this->table = new CustomersListTable();
	}

	protected function initPageActions() {
		if ( ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		$this->initEditAction();
		$this->initSaveAction();
		$this->initDeleteAction();
	}

	/**
	 * @param string $actionName edit|save|delete
	 *
	 * @return bool
	 */
	protected function isRequestAction( string $actionName ): bool {
		if ( ! isset( $_REQUEST['action'] ) ) {
			return false;
		}

		return $_REQUEST['action'] === $actionName;
	}

	/**
	 * @return bool
	 */
	protected function isRequestId(): bool {
		if ( isset( $_REQUEST['id'] ) && absint( $_REQUEST['id'] ) ) {
			return true;
		}

		return false;
	}

	protected function isActionEdit(): bool {
		if ( $this->isRequestAction( 'edit' ) &&
		     $this->isRequestId()
		) {
			return true;
		}

		return false;
	}

	protected function isActionSave(): bool {
		if ( $this->isActionEdit() &&
		     ( isset( $_REQUEST['save'] ) && ( 'Update' === $_REQUEST['save'] ) )
		) {
			return true;
		}

		return false;
	}

	protected function isActionDelete(): bool {
		if ( $this->isRequestAction( 'delete' ) &&
		     $this->isRequestId()
		) {
			return true;
		}

		return false;
	}

	protected function initEditAction() {
		if ( ! $this->isActionEdit() ) {
			return;
		}

		$customerId = absint( $_GET['id'] );

		if ( false !== $this->saved || ! $this->customer ) {
			$this->customer = mpapp()->repositories()->customer()->findById( $customerId );
		}

		if ( ! $this->customer ) {
			wp_die( __( 'Please, provide a valid Customer ID.', 'motopress-appointment' ) );
		}
	}

	/**
	 * todo: Need to do the correct validation of incoming values and display error messages.
	 */
	protected function initSaveAction() {

		if ( ! $this->isActionSave() ) {
			return;
		}

		$customerId = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : null;
		$userId     = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : null;
		$email      = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : null;
		$phone      = isset( $_POST['phone'] ) ? mpa_sanitize_phone( $_POST['phone'] ) : null;
		$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : null;

		if ( ! $customerId ) {
			wp_die( __( 'Please, provide a valid Customer ID.', 'motopress-appointment' ) );
		}

		if ( ! SecurityHandler::isUserCanEditCustomer() ) {
			wp_die( esc_html__( 'You do not have permission to do this action.', 'motopress-appointment' ) );
		}

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ) ) {
			wp_die( esc_html__( 'Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-appointment' ) );
		}

		if ( ( ! is_email( $email ) && '' !== $email ) ||
		     ( isset( $_POST['email'] ) && $email !== $_POST['email'] ) // case when after sanitization we get an empty string
		) {
			$this->errorMessages[] = esc_html__( 'Please, provide a valid email address.', 'motopress-appointment' );
		}

		$customerByEmail = mpapp()->repositories()->customer()->findByEmail( $email );
		if ( $customerByEmail && $customerByEmail->getId() !== $customerId ) {
			$this->errorMessages[] = esc_html__( 'This email is already registered. Please choose another one.', 'motopress-appointment' );
		}

		$customerByPhone = mpapp()->repositories()->customer()->findByPhone( $phone );
		if ( $customerByPhone && $customerByPhone->getId() !== $customerId ) {
			$this->errorMessages[] = esc_html__( 'This phone is already registered. Please choose another one.', 'motopress-appointment' );
		}

		$customerBeforeUpdate = mpapp()->repositories()->customer()->findById( $customerId );

		if ( ! $customerBeforeUpdate ) {
			wp_die( __( 'Please, provide a valid Customer ID.', 'motopress-appointment' ) );
		}

		$this->customer = clone $customerBeforeUpdate;

		if ( isset( $userId ) ) {
			$this->customer->setUserId( $userId );
		}

		if ( ! is_null( $name ) ) {
			$this->customer->setName( $name );
		}

		if ( ! is_null( $email ) ) {
			$this->customer->setEmail( $email );
		}

		if ( ! is_null( $phone ) ) {
			$this->customer->setPhone( $phone );
		}

		if ( $this->errorMessages ) {
			return;
		}

		if ( $this->customer !== $customerBeforeUpdate ) {
			try {
				$this->saved = mpapp()->repositories()->customer()->save( $this->customer );
			} catch ( \Exception $e ) {
				wp_die( $e->getMessage() );
			}
		}

		if ( $this->saved ) {
			$this->successMessage = esc_html__( 'Customer data updated.', 'motopress-appointment' );
		}

		if ( ! $this->saved && $this->customer != $customerBeforeUpdate ) {
			$this->errorMessages[] = esc_html__( "Customer data wasn't updated.", 'motopress-appointment' );
		}
	}

	protected function initDeleteAction() {
		if ( ! $this->isActionDelete() ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mpa-delete-customer' ) ) {
			wp_die( esc_html__( 'Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-appointment' ) );
		}

		if ( ! SecurityHandler::isUserCanDeleteCustomer() ) {
			wp_die( esc_html__( 'You do not have permission to do this action.', 'motopress-appointment' ) );
		}

		$customerId = absint( $_GET['id'] );

		if ( ! $customerId ) {
			wp_die( __( 'Please, provide a valid Customer ID.', 'motopress-appointment' ) );
		}

		try {
			$deleted = mpapp()->repositories()->customer()->delete( $customerId );
		} catch ( \Exception $e ) {
			wp_die( __( 'No customer was deleted.', 'motopress-appointment' ) );
		}

		if ( ! $deleted ) {
			wp_die( __( 'No customer was deleted.', 'motopress-appointment' ) );
		}

		$this->setCustomerSuccessDeletedNotice();

		wp_redirect( mpapp()->pages()->customers()->getUrl() );
	}

	protected function setCustomerSuccessDeletedNotice() {
		set_transient( self::CUSTOMER_SUCCESS_DELETED_NOTICE_TRANSIENT, true );
	}
}