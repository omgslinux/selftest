<?php

namespace App\Controller\Admin;

use App\Service\QuestionImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/admin/import', name: 'admin_import', methods: ['GET', 'POST'])]
class ImportController extends AbstractController
{
    public function __construct(
        private QuestionImporter $questionImporter
    ) {}

    public function __invoke(Request $request): Response
    {
        $session = $request->getSession();
        $result = $session->get('import_result');
        $error = $session->get('import_error');
        $session->remove('import_result');
        $session->remove('import_error');
        
        $method = $request->getMethod();
        
        if ('POST' === $method) {
            $selectedFile = $request->files->get('file');
            
            if ($selectedFile) {
                try {
                    $fileName = $selectedFile->getClientOriginalName();
                    $replace = $request->request->get('replace') === 'on';
                    $content = file_get_contents($selectedFile->getPathname());
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if ($extension === 'json') {
                        $result = $this->questionImporter->importJson($content, $replace);
                    } elseif ($extension === 'csv') {
                        $result = $this->questionImporter->importCsv($content, ';', $replace);
                    } else {
                        $error = 'Tipo de archivo no soportado. Usa JSON o CSV.';
                    }
                } catch (\Exception $e) {
                    $error = 'Error: ' . $e->getMessage();
                }
                
                if ($result || $error) {
                    $session->set('import_result', $result);
                    $session->set('import_error', $error);
                }
            }
            
            return $this->redirectToRoute('admin_import');
        }

        return $this->render('admin/import.html.twig', [
            'result' => $result,
            'error' => $error,
        ]);
    }
}