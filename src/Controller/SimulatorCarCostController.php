<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/tools/simulateur-voiture-electrique-ou-thermique',
    name: 'app_simulator_car_cost',
    methods: ['GET']
)]
final class SimulatorCarCostController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('simulator/car_cost.html.twig');
    }
}
