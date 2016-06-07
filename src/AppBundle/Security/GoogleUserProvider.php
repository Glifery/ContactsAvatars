<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;

class GoogleUserProvider extends BaseClass
{
    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->setGoogleId(null);
            $previousUser->setGoogleAccessToken(null);
        }

        //we connect current user
        $user->setGoogleId($username);
        $user->setGoogleAccessToken($response->getAccessToken());
        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $identifier = $response->getUsername();
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $identifier));

        //when the user is registrating
        if (null === $user) {
            // create new user here
            /** @var User $user */
            $user = $this->userManager->createUser();

            //I have set all requested data with the user's username
            //modify here with relevant data
            $user->setGoogleId($identifier);
            $user->setUsername($response->getEmail());
            $user->setFirstName($response->getFirstName());
            $user->setLastName($response->getLastName());
            $user->setEmail($response->getEmail());
            $user->setPassword($response->getEmail());
            $user->setEnabled(true);
            $user->setGoogleAccessToken($response->getAccessToken());
            $this->userManager->updateUser($user);

            return $user;
        }

        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);

        //update access token
        $user->setGoogleAccessToken($response->getAccessToken());

        return $user;
    }
}