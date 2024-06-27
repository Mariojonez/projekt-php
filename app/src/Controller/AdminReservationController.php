<?php

namespace App\Controller;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminReservationController extends AbstractController
{
    #[Route('/admin/reservation/{id}/approve', name: 'admin_reservation_approve')]
    public function approve(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $reservation->setStatus('approved');
        $entityManager->flush();

        // Update resource state
        // $resource = $reservation->getTask();
        // $resource->setStatus('unavailable');
        // $entityManager->flush();

        $this->addFlash('success', 'Reservation approved.');

        return $this->redirectToRoute('admin_reservation_list');
    }

    #[Route('/admin/reservation/{id}/reject', name: 'admin_reservation_reject')]
    public function reject(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $reservation->setStatus('rejected');
        $entityManager->flush();

        $this->addFlash('success', 'Reservation rejected.');

        return $this->redirectToRoute('admin_reservation_list');
    }

    #[Route('/admin/reservation/{id}/return', name: 'admin_reservation_return')]
    public function return(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $reservation->setStatus('returned');
        $entityManager->flush();

        // Update resource state
        // $resource = $reservation->getTask();
        // $resource->setStatus('available');
        // $entityManager->flush();

        $this->addFlash('success', 'Resource returned.');

        return $this->redirectToRoute('admin_reservation_list');
    }
}
