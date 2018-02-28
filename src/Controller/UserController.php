<?php

namespace StarterKit\StartBundle\Controller;

use StarterKit\StartBundle\Entity\BaseUser;
use StarterKit\StartBundle\Form\ChangePasswordType;
use StarterKit\StartBundle\Form\ForgetPasswordType;
use StarterKit\StartBundle\Form\RegisterType;
use StarterKit\StartBundle\Form\ResetPasswordType;
use StarterKit\StartBundle\Form\UpdateUserType;
use StarterKit\StartBundle\Form\UserImageType;
use StarterKit\StartBundle\Security\Voter\UserVoter;
use StarterKit\StartBundle\Service\AuthResponseServiceInterface;
use StarterKit\StartBundle\Service\FormSerializerInterface;
use StarterKit\StartBundle\Tests\Entity\User;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use StarterKit\StartBundle\Service\FileUploadInterface;
use StarterKit\StartBundle\Service\UserServiceInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package ApiBundle\Controller\Api
 */
class UserController extends BaseRestController
{
    /**
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @var AuthResponseServiceInterface
     */
    protected $authResponseService;

    /**
     * @var FileUploadInterface
     */
    protected $s3Service;

    /**
     * UserController constructor.
     * @param FormSerializerInterface $formSerializer
     * @param UserServiceInterface $userService
     * @param AuthResponseServiceInterface $authResponseService
     * @param FileUploadInterface $s3Service
     */
    public function __construct(FormSerializerInterface $formSerializer,
                                UserServiceInterface $userService,
                                AuthResponseServiceInterface $authResponseService,
                                FileUploadInterface $s3Service)
    {
        parent::__construct($formSerializer);
        $this->userService = $userService;
        $this->authResponseService = $authResponseService;
        $this->s3Service = $s3Service;
    }


    /**
     * @SWG\Post(
     *     tags={"users"},
     *     description="Register's the user",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="post body",
     *          in="body",
     *          type="json",
     *          description="Register Post Body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="plainPassword", type="string")
     *          )
     *     ),
     *     @SWG\Response(description="Success", response="201"),
     *     @SWG\Response(description="Failed", response="403"),
     *     @SWG\Response(description="Validation Errors", response="400"),
     *     @SWG\Response(description="Nothing was sent", response="401")
     * )
     * @Route(path="/users", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(RegisterType::class);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->userService->registerUser($form->getData());

            return $this->authResponseService->createJsonAuthResponse($user);
        }

        return $this->serializeFormError($form);
    }

    /**
     * @SWG\Patch(
     *     tags={"users"},
     *     description="Register's the user",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="post body",
     *          in="body",
     *          type="json",
     *          description="Update User Post Body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="displayName", type="string"),
     *              @SWG\Property(property="bio", type="string")
     *          )
     *     ),
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          type="string",
     *          description="The user's id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          name="Authorization",
     *          in="header",
     *          type="string",
     *          description="The user's jwt token",
     *          default="Bearer "
     *     ),
     *     @SWG\Response(description="Success", response="201"),
     *     @SWG\Response(description="Failed", response="403"),
     *     @SWG\Response(description="Validation Errors", response="400"),
     *     @SWG\Response(description="Nothing was sent", response="401")
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @param integer $id
     *
     * @Route(path="/users/{id}", methods={"PATCH"})
     *
     * @return FormInterface|Response
     */
    public function updateUserAction(Request $request, $id)
    {
        $user = $this->getUserById($id);

        $form = $this->createForm(UpdateUserType::class, $user);

        $form->submit(json_decode($request->getContent(), true));


        if ($form->isSubmitted() && $form->isValid()) {

            /** @var BaseUser $user */
            $user = $form->getData();

            $this->denyAccessUnlessGranted(UserVoter::USER_CAN_VIEW_EDIT, $user);

            $this->userService->save($user);

            return $this->serializeSingleObject($user->singleView(), BaseUser::RESPONSE_TYPE,  Response::HTTP_OK);
        }

        return $this->serializeFormError($form);
    }

    /**
     * @SWG\Post(
     *     tags={"users"},
     *     description="Request's password reset token and sends email to user.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="post body",
     *          in="body",
     *          type="json",
     *          description="Forget Password Post Body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="email", type="string")
     *          )
     *     ),
     *     @SWG\Response(description="Success", response="204"),
     *     @SWG\Response(description="Failed", response="403"),
     *     @SWG\Response(description="Validation Errors", response="400")
     * )
     *
     * @Route(path="/users/forget-password", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\Form\Form|Response
     */
    public function forgetPasswordAction(Request $request)
    {
        $form = $this->createForm(ForgetPasswordType::class);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->forgetPassword($form->getData());

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return $this->serializeFormError($form);
    }

    /**
     * @SWG\Patch(
     *     tags={"users"},
     *     description="Reset's the user's password",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="post body",
     *          in="body",
     *          type="json",
     *          description="Reset Password Post Body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="plainPassword", type="string")
     *          )
     *     ),
     *     @SWG\Parameter(
     *          name="token",
     *          in="path",
     *          type="string",
     *          description="THe password reset token.",
     *          required=true,
     *     ),
     *     @SWG\Response(description="Success", response="204"),
     *     @SWG\Response(description="Failed", response="403"),
     *     @SWG\Response(description="Validation Errors", response="400")
     * )
     *
     * @Route(path="/users/reset-password/{token}", methods={"PATCH"})
     * @param Request $request
     * @param string $token
     *
     * @return Response|FormInterface
     */
    public function resetPasswordAction(Request $request, $token)
    {
        $user = $this->userService->findUserByForgetPasswordToken(urldecode($token));

        if (empty($user)) {

            throw $this->createNotFoundException('Invalid Token');
        }

        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->saveUserForResetPassword($user);

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return $this->serializeFormError($form);
    }


    /**
     *
     * @SWG\Patch(
     *     tags={"users"},
     *     description="Reset's the user's password",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="post body",
     *          in="body",
     *          type="json",
     *          description="Reset Password Post Body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="currentPassword", type="string", description="Not required for ROLE_ADMIN
     * user's"),
     *              @SWG\Property(property="newPassword", type="string")
     *          )
     *     ),
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          type="string",
     *          description="The user's id.",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          name="Authorization",
     *          in="header",
     *          type="string",
     *          description="The user's jwt token",
     *          default="Bearer "
     *     ),
     *     @SWG\Response(description="Success", response="204"),
     *     @SWG\Response(description="Failed", response="403"),
     *     @SWG\Response(description="Validation Errors", response="400")
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @Route(path="/users/{id}/password", methods={"PATCH"})
     *
     * @param Request $request
     * @param integer $id
     *
     * @return FormInterface|Response
     */
    public function changePasswordAction(Request $request, $id)
    {
        $user = $this->getUserById($id);

        $this->denyAccessUnlessGranted(UserVoter::USER_CAN_VIEW_EDIT, $user);

        $form = $this->createForm(ChangePasswordType::class);

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPlainPassword($form->get('newPassword')->getData());
            $this->userService->saveUserWithPlainPassword($user);

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return $this->serializeFormError($form);
    }

    /**
     * @SWG\Post(
     *     tags={"users"},
     *     description="Updates / Creates the user's profile image.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="image",
     *          in="formData",
     *          type="file",
     *          description="The user's profile image.",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          type="string",
     *          description="The user's id.",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          name="Authorization",
     *          in="header",
     *          type="string",
     *          required=true,
     *          description="The user's jwt token",
     *          default="Bearer "
     *     ),
     *     @SWG\Response(description="Success", response="204"),
     *     @SWG\Response(description="Failed", response="403"),
     *     @SWG\Response(description="Validation Errors", response="400")
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @Route(path="/users/{id}/image", methods={"POST"})
     *
     * @param Request $request
     * @param integer $id
     *
     * @return Response
     */
    public function imageAction(Request $request, $id)
    {
        /** @var User $user */
        $user = $this->getUserById($id);

        $this->denyAccessUnlessGranted(UserVoter::USER_CAN_VIEW_EDIT, $user);

        $form = $this->createForm(UserImageType::class, $user);

        $form->submit(['image' => $request->files->get('image')]);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileUploadModel = $this->s3Service->uploadFileWithFolderAndName(
                $user->getImage(),
                'profile_pics',
                md5($user->getId() . '_profile_id')
            );
            $user->setImageUrl($fileUploadModel->getUrl())
                ->setImageVendor($fileUploadModel->getVendor())
                ->setImageId($fileUploadModel->getFileId());
            $this->userService->save($user);

            return new Response('', Response::HTTP_NO_CONTENT);
        }


        return $this->serializeFormError($form);
    }


    /**
     * @SWG\Get(
     *     tags={"users"},
     *     description="Get's a single user.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          type="string",
     *          description="The user's id.",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          name="Authorization",
     *          in="header",
     *          type="string",
     *          required=true,
     *          description="The user's jwt token",
     *          default="Bearer "
     *     ),
     *     @SWG\Response(description="Success", response="204"),
     *     @SWG\Response(description="Failed", response="403"),
     *     @SWG\Response(description="Validation Errors", response="400")
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @param integer $id
     * @Route(path="/users/{id}", methods={"GET"})
     *
     * @return Response
     */
    public function getUserAction($id)
    {
        $user = $this->getUserById($id);
        $this->denyAccessUnlessGranted(UserVoter::USER_CAN_VIEW_EDIT, $user);

        return $this->serializeSingleObject($user->singleView(), BaseUser::RESPONSE_TYPE);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @SWG\Get(
     *     tags={"users"},
     *     description="Get's a multiple user.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *          name="Authorization",
     *          in="header",
     *          type="string",
     *          required=true,
     *          description="The user's jwt token",
     *          default="Bearer "
     *     ),
     *     @SWG\Response(description="Success", response="204"),
     *     @SWG\Response(description="Failed", response="403"),
     *     @SWG\Response(description="Validation Errors", response="400")
     * )
     *
     * @Route(path="/users", methods={"GET"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getUsersAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        $users = $this->userService->searchUser(
                $request->query->get('q'),
                $page
            );

        return $this->serializeList($users, BaseUser::RESPONSE_TYPE, $page);
    }

    /**
     * Gets the user by the user's id
     *
     * @param $id
     * @return null|object|BaseUser
     */
    private function getUserById($id)
    {
        $user = $this->userService->findUserById($id);

        if (empty($user)) {
            throw $this->createNotFoundException('user not found');
        }

        return $user;
    }

}