<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectController extends AbstractController
{
    #[Route('/project', name: 'project')]
    public function index(): Response
    {
        return $this->render('project/index.html.twig');
    }

    #[Route('/admin/project', name: 'admin_project')]
    public function adminIndex(ProjectRepository $projectRepository): Response
    {
        return $this->render('project/adminList.html.twig', [
            'project' => $projectRepository->findAll()
        ]);
    }

    #[Route('admin/project/create', name:'project_create')]
    public function create(Request $request, SluggerInterface $slugger, ManagerRegistry $managerRegistry): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $infoImg = $form['img']->getData();
            if (!empty($infoImg)) {
                $extensionImg = $infoImg->guessExtension();
                $nomImg = time() . '.' . $extensionImg;
                $project->setImg($nomImg);
                $infoImg->move($this->getParameter('category_img_dir'), $nomImg);
            }

            $manager = $managerRegistry->getManager();
            $manager->persist($project);
            $manager->flush();

            return $this->redirectToRoute('admin_project');
        }
        return $this->render('project/create.html.twig', [
            'projectForm' => $form->createView()
        ]);
    }


}
