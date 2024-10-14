<?php

namespace App\Http\Controllers\MonitoringProduksi;

use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Services\TeamVerifikasiService;

class StatusVerifikasiTeamController extends Controller
{
    private $teamService;

    public function __construct(TeamVerifikasiService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function index()
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/TeamList', [
            'workstations' => $this->teamService->getAllWorkstations()
        ]);
    }

    public function show(string $id)
    {
        $data = $this->teamService->getProductAndLabelsByTeam($id);

        if ($data) {
            return $this->renderMonitorView($data);
        }

        return $this->renderTeamListView();
    }

    private function renderMonitorView($data)
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/Monitor', [
            'spec' => $data['product'],
            'dataRim' => $data['labels'],
            'team' => $data['team'],
        ]);
    }

    private function renderTeamListView()
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/TeamList', [
            'workstations' => $this->teamService->getAllWorkstations()
        ]);
    }
}
