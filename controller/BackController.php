<?php

namespace keymener\myblog\controller;

use keymener\myblog\core\Authentication;
use keymener\myblog\core\Csrf;
use keymener\myblog\core\TwigLaunch;
use keymener\myblog\entity\User;
use keymener\myblog\model\UserManager;

/**
 * this is where the admin part is begining
 *
 * @author keymener
 */
class BackController
{

    private $twig;
    private $auth;
    private $user;
    private $userManager;
    private $csrf;

    public function __construct(TwigLaunch $twig, Authentication $auth, User $user, UserManager $userManager, Csrf $csrf)
    {
        $this->twig = $twig;
        $this->auth = $auth;
        $this->user = $user;
        $this->userManager = $userManager;
        $this->csrf = $csrf;
    }

    /**
     * login page
     */
    public function login()
    {
        if (isset($_POST['username'], $_POST['password'], $_SESSION['token'], $_POST['token']) && $this->userManager->userExists($_POST['username'])) {

            if ($_SESSION['token'] == $_POST['token']) {
                $pwd = $_POST['password'];

                // get all info from user
                $dataUser = $this->userManager->getUser($_POST['username']);

                // hydrate the instance user with all info
                $this->user->hydrate($dataUser);


                //compare password
                if ($this->auth->checkPassword($pwd, $this->user->getPassword())) {
                    $_SESSION['userId'] = $this->user->getId();
                    $_SESSION['username'] = $this->user->getFirstname();

                    header('Location:/back/home');
                } else {
                    //generate token csrf
                    $token = $this->csrf->sessionRandom(5);

                    $twig = $this->twig->twigLoad();
                    echo $twig->render('backend/login.twig', array(
                        'message' => 'error',
                        'token' => $token
                    ));
                }
            } else {

               header('Location: /error/error/500');
            }
        } else {
            //generate token csrf
            $token = $this->csrf->sessionRandom(5);

            $twig = $this->twig->twigLoad();
            echo $twig->render('backend/login.twig', array(
                'message' => 'empty',
                'token' => $token
            ));
        }
    }

//
    /**
     * home page of backend
     */
    public function home()
    {

        $twig = $this->twig->twigLoad();
        echo $twig->render('backend/home.twig', array(
            'message' => false));
    }

    /**
     * logout
     */
    public function logout()
    {
        $this->auth->logout();
        $twig = $this->twig->twigLoad();
        echo $twig->render('backend/login.twig', array('p' => null));
    }

    /**
     * comment managment page
     */
    public function comments()
    {

        $twig = $this->twig->twigLoad();
        echo $twig->render('backend/home.twig', array('message' => false));
    }

    /**
     * user managment page
     */
    public function users()
    {

        $users = $this->userManager->getAllUsers();

        $twig = $this->twig->twigLoad();
        echo $twig->render('backend/user.twig', array('users' => $users,));
    }

}
