<?php

namespace Dragonfly\NovaposhtaBasic\Controller\Adminhtml\ApiCity;

use Dragonfly\NovaposhtaBasic\Api\Data\ApiCityInterface;
use Dragonfly\NovaposhtaBasic\Command\ApiCity\DeleteByIdCommand;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Delete ApiCity controller.
 */
class Delete extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Dragonfly_NovaposhtaBasic::city';

    /**
     * @var DeleteByIdCommand
     */
    private DeleteByIdCommand $deleteByIdCommand;

    /**
     * @param Context $context
     * @param DeleteByIdCommand $deleteByIdCommand
     */
    public function __construct(
        Context           $context,
        DeleteByIdCommand $deleteByIdCommand
    )
    {
        parent::__construct($context);
        $this->deleteByIdCommand = $deleteByIdCommand;
    }

    /**
     * Delete ApiCity action.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var ResultInterface $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        $entityId = (int)$this->getRequest()->getParam(ApiCityInterface::API_CITY_ID);

        try {
            $this->deleteByIdCommand->execute($entityId);
            $this->messageManager->addSuccessMessage(__('You have successfully deleted ApiCity entity'));
        } catch (CouldNotDeleteException | NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect;
    }
}
