<?php

namespace AmeliaBooking\Application\Commands\User\Customer;

use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\User\CustomerApplicationService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;

/**
 * Class AddCustomerCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\User\Customer
 */
class AddCustomerCommandHandler extends CommandHandler
{

    public $mandatoryFields = [
        'type',
        'firstName',
        'lastName',
        'email'
    ];

    /**
     * @param AddCustomerCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle(AddCustomerCommand $command)
    {
        if (!$command->getPermissionService()->currentUserCanWrite(Entities::CUSTOMERS)) {
            throw new AccessDeniedException('You are not allowed to perform this action!');
        }

        /** @var CustomerApplicationService $customerAS */
        $customerAS = $this->container->get('application.user.customer.service');

        $this->checkMandatoryFields($command);

        if ($command->getField('externalId') === -1) {
            $command->setField('externalId', null);
        }

        $userData = $command->getFields();

        $userData = apply_filters('amelia_before_customer_added_filter', $userData);

        do_action('amelia_before_customer_added', $userData);

        $response = $customerAS->createCustomer($userData);

        do_action('amelia_after_customer_added', $response? $response->getData() : null);

        return $response;
    }
}
