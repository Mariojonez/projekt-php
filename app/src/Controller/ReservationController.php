<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\Type\ChangeStatusType;
use App\Form\Type\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReservationController extends AbstractController
{
    /**
     * Constructor.
     */
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    #[Route('/reservation/new', name: 'reservation_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedException('You must be logged in to create a reservation.');
        }

        $user = $this->getUser();
        $reservation = new Reservation();
        $reservation->setUser($user);

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedTask = $form->get('task')->getData();
            $reservation->setTask($selectedTask);

            $reservation->setStatus('pending');

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('reservation_list');
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reservations', name: 'reservation_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        // Get the current logged-in user
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view reservations.');
        }

        // Check if the user is an admin
        if ($this->isGranted('ROLE_ADMIN')) {
            // Fetch all reservations
            $reservations = $entityManager->getRepository(Reservation::class)->findAll();
        } else {
            // Fetch reservations associated with the current user
            $reservations = $entityManager->getRepository(Reservation::class)
                ->findBy(['user' => $user]);
        }

        return $this->render('reservation/list.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Change status action.
     *
     * @param Request $request HTTP request
     * @param Reservation    $reservation   Reservation entity
     *
     * @return Response HTTP response
     */
    #[Route('/reservations/{id}/change-status', name: 'reservation_change_status', methods: ['GET', 'PUT'])]
    #[IsGranted('CHANGE_STATUS', subject: 'reservation')]
    public function changeStatus(Request $request, Reservation $reservation): Response
    {
        $form = $this->createForm(
            ChangeStatusType::class,
            $reservation,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('reservation_change_status', ['id' => $reservation->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('reservation_list');
        }

        return $this->render(
            'reservation/change_status.html.twig',
            [
                'form' => $form->createView(),
                'reservation' => $reservation,
            ]
        );
    }
}
