<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Enum\Authentification;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Authentification::FULLY->value)]
class ChangePasswordController extends AbstractController
{
    #[Route('/profile/change-password', name: 'app_change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Votre mot de passe a  t  modifi  avec succ s !<br>' .
                'Pour votre s curit , pensez  utiliser un mot de passe unique et robuste.'
            );

            return $this->redirectToRoute('app_user_account');
        }

        // Affichage du formulaire avec erreurs éventuelles
        return $this->render('user/change_password.html.twig', [
            'form' => $form,
        ]);
    }
}
