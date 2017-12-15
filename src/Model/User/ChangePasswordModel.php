<?php

namespace StarterKit\StartBundle\Model\User;

use Symfony\Component\Validator\Constraints as Constraints;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use StarterKit\StartBundle\Entity\BaseUser;

/**
 * Class ChangePasswordModel
 * @package StarterKit\StartBundle\Model\User
 */
class ChangePasswordModel
{
    /**
     * This validation group is only applied when the user is not admin
     * @var string
     */
    const VALIDATE_CURRENT_PASSWORD_GROUP = 'validate_current_password_group';

    /**
     * This is applied to all the validation groups
     * @var string
     */
    const DEFAULT_CHANGE_PASSWORD_VALIDATION_GROUP = 'validate_default_group';

    /**
     * @var string
     * @UserPassword(
     *     message="The password you entered does not match your current password.",
     *     groups={ChangePasswordModel::VALIDATE_CURRENT_PASSWORD_GROUP})
     * @Constraints\NotBlank(groups={ChangePasswordModel::VALIDATE_CURRENT_PASSWORD_GROUP})
     */
    private $currentPassword;

    /**
     * @Constraints\NotBlank(groups={ChangePasswordModel::DEFAULT_CHANGE_PASSWORD_VALIDATION_GROUP})
     * @Constraints\Length(
     *     min=BaseUser::MIN_PASSWORD_LENGTH,
     *     max=BaseUser::MAX_PASSWORD_LENGTH,
     *     groups={ChangePasswordModel::DEFAULT_CHANGE_PASSWORD_VALIDATION_GROUP}
     * )
     * @var string
     */
    private $newPassword;

    /**
     * @return string
     */
    public function getCurrentPassword()
    {
        return $this->currentPassword;
    }

    /**
     * @param string $currentPassword
     * @return ChangePasswordModel
     */
    public function setCurrentPassword($currentPassword)
    {
        $this->currentPassword = $currentPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }

    /**
     * @param string $newPassword
     * @return ChangePasswordModel
     */
    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;

        return $this;
    }


}