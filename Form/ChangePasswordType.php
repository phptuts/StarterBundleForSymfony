<?php

namespace StarterKit\StartBundle\Form;

use StarterKit\StartBundle\Model\User\ChangePasswordModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Form\Extension\Core\Type as Types;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Class ChangePasswordType
 * @package StarterKit\StartBundle\Form\User
 */
class ChangePasswordType extends AbstractType
{
    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newPassword', Types\PasswordType::class)
            ->add('currentPassword', Types\PasswordType::class);

        // If the user is role admin entering a a password is not required
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder->remove('currentPassword');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $validationGroups = [ChangePasswordModel::DEFAULT_CHANGE_PASSWORD_VALIDATION_GROUP];
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $validationGroups[] = ChangePasswordModel::VALIDATE_CURRENT_PASSWORD_GROUP;
        }

        $resolver->setDefaults([
            'data_class' => ChangePasswordModel::class,
            'validation_groups' => $validationGroups,
            'csrf_protection' => false,
        ]);
    }
}