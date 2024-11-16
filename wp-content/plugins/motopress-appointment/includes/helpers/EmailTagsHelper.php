<?php

namespace MotoPress\Appointment\Helpers;

use MotoPress\Appointment\Emails\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
final class EmailTagsHelper {

	protected static function General(): Tags\InterfaceTags {
		$name        = 'general';
		$description = esc_html__( 'General tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}

	protected static function Booking(): Tags\InterfaceTags {
		$name        = 'booking';
		$description = esc_html__( 'Booking tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}

	protected static function Payment(): Tags\InterfaceTags {
		$name        = 'payment';
		$description = esc_html__( 'Payment tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}

	protected static function Customer(): Tags\InterfaceTags {
		$name        = 'customer';
		$description = esc_html__( 'Customer tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}

	/**
	 * @since 1.18.0
	 */
	protected static function CustomerAccount(): Tags\InterfaceTags {
		$name        = 'customer-account';
		$description = esc_html__( 'Customer account tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}

	protected static function Reservation(): Tags\InterfaceTags {
		$name        = 'reservation';
		$description = esc_html__( 'Reservation tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}


	protected static function Service(): Tags\InterfaceTags {
		$name        = 'service';
		$description = esc_html__( 'Service tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}


	protected static function Employee(): Tags\InterfaceTags {
		$name        = 'employee';
		$description = esc_html__( 'Employee tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}


	protected static function Location(): Tags\InterfaceTags {
		$name        = 'location';
		$description = esc_html__( 'Location tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}

	protected static function TemplatePart(): Tags\InterfaceTags {
		$name        = 'template-part';
		$description = esc_html__( 'Template part tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}

	protected static function Action(): Tags\InterfaceTags {
		$name        = 'action';
		$description = esc_html__( 'Action tags', 'motopress-appointment' );

		return new Tags\TagsGroup( $name, $description );
	}


	protected static function GeneralGroup() {
		return self::General()
				->add( new Tags\General\AppointmentTag() )
				->add( new Tags\General\SiteTitleTag() )
				->add( new Tags\General\SiteLinkTag() );
	}

	protected static function BookingGroup( bool $isAdmin = false ) {
		$tags = self::Booking()
					->add( new Tags\Booking\BookingIdTag() )
					->add( new Tags\Booking\BookingCouponCodeTag() )
					->add( new Tags\Booking\BookingCouponDiscountTag() )
					->add( new Tags\Booking\BookingTotalPriceTag() )
					->add( new Tags\Booking\BookingAlreadyPaidTag() )
					->add( new Tags\Booking\BookingLeftToPayTag() )
					->add( new Tags\Booking\BookingDateTag() );
		if ( $isAdmin ) {
			$tags->add( new Tags\Booking\BookingEditLinkTag() );
		}

		return $tags;
	}

	protected static function CustomerGroup() {
		return self::Customer()
				->add( new Tags\Booking\BookingCustomerNameTag() )
				->add( new Tags\Booking\BookingCustomerEmailTag() )
				->add( new Tags\Booking\BookingCustomerPhoneTag() )
				->add( new Tags\Booking\BookingCustomerNotesTag() );
	}

	protected static function ReservationGroup() {
		return self::Reservation()
				->add( new Tags\Reservation\ReservationPriceTag() )
				->add( new Tags\Reservation\ReservationDateTag() )
				->add( new Tags\Reservation\ReservationTimeTag() )
				->add( new Tags\Reservation\ReservationBufferTimeTag() )
				->add( new Tags\Reservation\ReservationStartTimeTag() )
				->add( new Tags\Reservation\ReservationEndTimeTag() )
				->add( new Tags\Reservation\ReservationEndBufferTimeTag() )
				->add( new Tags\Reservation\ReservationStartBufferTimeTag() )
				->add( new Tags\Reservation\ReservationClientsNumberTag() );
	}

	protected static function ServiceGroup(): Tags\InterfaceTags {
		return self::Service()
				->add( new Tags\Service\ServiceIdTag() )
				->add( new Tags\Service\ServiceNameTag() )
				->add( new Tags\Service\ServiceDescriptionTag() )
				->add( new Tags\Service\ServiceCategoriesTag() )
				->add( new Tags\Service\ServiceLinkTag() )
				->add( new Tags\Service\ServiceNotificationNotice1Tag() )
				->add( new Tags\Service\ServiceNotificationNotice2Tag() );
	}

	protected static function EmployeeGroup(): Tags\InterfaceTags {
		return self::Employee()
				->add( new Tags\Employee\EmployeeIdTag() )
				->add( new Tags\Employee\EmployeeNameTag() )
				->add( new Tags\Employee\EmployeeBioTag() )
				->add( new Tags\Employee\EmployeeLinkTag() );
	}

	protected static function LocationGroup(): Tags\InterfaceTags {
		return self::Location()
				->add( new Tags\Location\LocationIdTag() )
				->add( new Tags\Location\LocationNameTag() )
				->add( new Tags\Location\LocationDescriptionTag() )
				->add( new Tags\Location\LocationCategoriesTag() )
				->add( new Tags\Location\LocationLinkTag() );
	}

	protected static function CancelationDetailsTag(): Tags\InterfaceTag {

		$cancelationDetailsTag = new Tags\TemplatePart\CancelationDetailsTag();
		if ( ! mpapp()->settings()->isUserCanBookingCancellation() ) {
			$cancelationDetailsTag = new Tags\EmptyContentTag( $cancelationDetailsTag );
		}

		return $cancelationDetailsTag;
	}

	public static function AdminEmailTags(): Tags\InterfaceTags {

		$tags = new Tags\Tags();

		return $tags
			->add( self::GeneralGroup() )
			->add( self::BookingGroup( true ) )
			->add( self::CustomerGroup() )
			->add(
				self::TemplatePart()->add( new Tags\TemplatePart\Admin\ReservationsDetailsTag() )
			);
	}

	public static function AdminEmailWithPaymentsTags(): Tags\InterfaceTags {

		return self::AdminEmailTags()->add(
			self::TemplatePart()
				->add( new Tags\TemplatePart\Admin\ReservationsDetailsTag() )
				->add( new Tags\TemplatePart\Admin\BookingPaymentsTag() )
		);
	}

	public static function CustomerEmailTags(): Tags\InterfaceTags {

		$tags = new Tags\Tags();

		return $tags
			->add( self::GeneralGroup() )
			->add( self::BookingGroup( false ) )
			->add( self::CustomerGroup() )
			->add(
				self::TemplatePart()
					->add( new Tags\TemplatePart\Customer\ReservationsDetailsTag() )
					->add( self::cancelationDetailsTag() )
			);
	}

	public static function CustomerEmailConfirmationUponPaymentTags(): Tags\InterfaceTags {

		$customerEmailTags    = self::CustomerEmailTags();
		$templatePartTagsName = self::TemplatePart()->getName();
		$tags                 = $customerEmailTags->getTags();

		if ( isset( $tags[ $templatePartTagsName ] ) ) {
			$templatePartTags = $tags[ $templatePartTagsName ]->add( new Tags\TemplatePart\Customer\BookingPaymentsTag() );
			$customerEmailTags->add( $templatePartTags );
		}

		return $customerEmailTags;
	}

	public static function CustomerEmailCancelledBookingTags(): Tags\InterfaceTags {

		$customerEmailTags    = self::CustomerEmailTags();
		$templatePartTagsName = self::TemplatePart()->getName();
		$tags                 = $customerEmailTags->getTags();

		if ( isset( $tags[ $templatePartTagsName ] ) ) {
			$templatePartTags = $tags[ $templatePartTagsName ]->remove( self::CancelationDetailsTag() );
			$customerEmailTags->add( $templatePartTags );
		}

		return $customerEmailTags;
	}

	/**
	 * @since 1.18.0
	 */
	public static function CustomerEmailAccountCreationTags(): Tags\InterfaceTags {

		$tags = new Tags\Tags();

		$customerGroup = self::Customer()
		                     ->add( new Tags\Customer\CustomerNameTag() )
		                     ->add( new Tags\Customer\CustomerEmailTag() )
		                     ->add( new Tags\Customer\CustomerPhoneTag() );

		$customerAccountGroup = self::CustomerAccount()
		                            ->add( new Tags\Customer\CustomerAccountLinkTag() )
		                            ->add( new Tags\Customer\CustomerAccountLoginTag() )
		                            ->add( new Tags\Customer\CustomerAccountPasswordTag() );

		return $tags
			->add( self::GeneralGroup() )
			->add( $customerGroup )
			->add( $customerAccountGroup );
	}

	public static function NotificationTags(): Tags\InterfaceTags {
		$tags              = new Tags\Tags();
		$templatePartGroup = self::TemplatePart()
								->add( new Tags\TemplatePart\Customer\BookingPaymentsTag() )
								->add( self::cancelationDetailsTag() );

		return $tags
			->add( self::GeneralGroup() )
			->add( self::BookingGroup( false ) )
			->add( self::CustomerGroup() )
			->add( self::ReservationGroup() )
			->add( self::EmployeeGroup() )
			->add( self::ServiceGroup() )
			->add( self::LocationGroup() )
			->add( $templatePartGroup );
	}

	public static function CustomerPaymentDetailsTemplatePartTags(): Tags\InterfaceTags {

		$tags = self::Payment()
					->add( new Tags\Payment\PaymentIdTag() )
					->add( new Tags\Payment\PaymentMethodTag() )
					->add( new Tags\Payment\PaymentAmountTag() )
					->add( new Tags\Payment\PaymentInstructionsTag() );

		return $tags;
	}

	public static function AdminPaymentDetailsTemplatePartTags(): Tags\InterfaceTags {

		$tags = self::Payment()
					->add( new Tags\Payment\PaymentIdTag() )
					->add( new Tags\Payment\PaymentMethodTag() )
					->add( new Tags\Payment\PaymentAmountTag() )
					->add( new Tags\Payment\PaymentEditLinkTag() );

		return $tags;
	}

	public static function CustomerBookingCancellationTemplatePartTags(): Tags\InterfaceTags {
		return self::Action()->add( new Tags\Action\BookingCancelLinkTag() );
	}

	public static function ReservationDetailsTemplatePartTags(): Tags\InterfaceTags {

		$tags = new Tags\Tags();

		return $tags->add( self::ReservationGroup() )
					->add( self::EmployeeGroup() )
					->add( self::ServiceGroup() )
					->add( self::LocationGroup() );
	}
}
