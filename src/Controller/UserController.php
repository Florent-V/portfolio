<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\UserUpdateFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::USER->value)]
class UserController extends AbstractController
{
    #[Route('/account', name: 'app_user_account', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
    ): Response {

        /**
         * @var ?User $user
         */
        $user = $this->getUser();


        return $this->render('user/show.html.twig', [
            'user' => $userRepository->findOneBy([
                'id' => $user->getId(),
            ]),
        ]);
    }

    #[Route('/account/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        /**
         * @var ?User $user
         */
        $user = $this->getUser();

        $form = $this->createForm(UserUpdateFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            // Évite la tentative de serialization du File dans la session
            $user->setPictureFile(null);

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_user_account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/account/delete/', name: 'app_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {

        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $user->setEmail('deleteduser@mail.fr');
            $uniqId = uniqid();
            $user->setUserName('deleteduser' . $uniqId);
            $user->setFirstName('deleteduser' . $uniqId);
            $user->setLastName('deleteduser' . $uniqId);
            $user->setPhone('deleteduser' . $uniqId);

            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logout', [], Response::HTTP_SEE_OTHER);
    }
}
