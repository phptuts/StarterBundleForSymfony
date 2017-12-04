# How to log the user in after they have registered

This one stupidly simple.  Just inject AuthResponseServiceInterface into the controller and authenticate the response.  [Example](https://github.com/phptuts/starter-bundle-example/blob/master/src/AppBundle/Controller/UserController.php)

``` 
   /**
     * @Route("/register", name="register")
     * @param  Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {

        $form = $this->createForm(RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userService->registerUser($form->getData());

            $response = $this->redirectToRoute('homepage');

            return $this->authResponseService->authenticateResponse($user, $response);
        }

        return $this->render('register/register.html.twig', [
            'registerForm' => $form->createView()
        ]);
    }
```