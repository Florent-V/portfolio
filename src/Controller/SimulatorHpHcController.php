<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/tools/simulateur-heures-creuses',
    name: 'app_simulator_hphc',
    methods: ['GET']
)]
final class SimulatorHpHcController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('simulator/hp_hc.html.twig');
    }
}
