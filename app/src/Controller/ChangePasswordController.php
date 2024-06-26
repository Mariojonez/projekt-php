<?php

namespace App\Controller;

use App\Form\Type\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangePasswordController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Route('/change-password', name: 'change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        // Ensure the user is logged in
        if (!$user || !$user instanceof PasswordAuthenticatedUserInterface) {
            throw new AccessDeniedException($this->translator->trans('message.must_be_logged_in'));
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check current password validity
            $currentPassword = $form->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', $this->translator->trans('message.password_does_not_match'));
                return $this->redirectToRoute('change_password');
            }

            // Get new password and confirm password
            $newPassword = $form->get('newPassword')->getData();
            $confirmNewPassword = $form->get('confirmPassword')->getData();

            // Check if new password matches confirmed new password
            if ($newPassword !== $confirmNewPassword) {
                $this->addFlash('error', $this->translator->trans('message.confirm_password_does_not_match'));
                return $this->redirectToRoute('change_password');
            }

            // Hash and set the new password
            $newPassword = $form->get('newPassword')->getData();
            $encodedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($encodedPassword);

            // Save the updated user entity
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('task_index'); // Redirect to profile or another appropriate route
        }

        return $this->render('change_password/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}