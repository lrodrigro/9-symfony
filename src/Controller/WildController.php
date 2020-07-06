<?php
// src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Program;
use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Episode;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WildController extends AbstractController
{
    /**
     * @Route("/", name="wild_index")
    */
    public function index() :Response
    {
        $programs = $this->getDoctrine()
          ->getRepository(Program::class)
          ->findAll();
          
        if (!$programs) {
            throw $this->createNotFoundException(
            'No program found in program\'s table.'
            );
        }

        foreach($programs as $program)
        {
            //echo $program->getTitle();
            $program->url = preg_replace(
                '/ /',
                '-', mb_strtolower(trim(strip_tags($program->getTitle()), "-")));
        }
        //var_dump($programs);
        
        //var_dump($programs);
        return $this->render(
            'wild/index.html.twig',
            ['programs' => $programs]
        );
    }

    /**
    * Getting a program with a formatted slug for title
    *
    * @param string $slug The slugger
    * @Route("/show/{slug<^[a-z0-9-]+$>}", defaults={"slug" = null}, name="show")
    * @return Response
    */

    public function show(?string $slug): Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }

        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );

        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with '.$slug.' title, found in program\'s table.'
            );
        }

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug'  => $slug,
        ]);
    }

    /**
    * Getting a category
    *
    * @Route("/category/{categoryName}", name="show_category")
    * @return Response
    */

    public function showByCategory(string $categoryName = ''): Response
    {
        if (!$categoryName) {
            throw $this
                ->createNotFoundException('No category has been sent to find a program in program\'s table.');
        }

        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(['name' => $categoryName]);
        
        if (!$category) {
            throw $this->createNotFoundException(
                'No category with '.$categoryName.' title, found in category\'s table.'
            );
        }
        
        $programs = $this->getDoctrine()
        ->getRepository(Program::class)
        ->findBy(['category' => $category],['id'=>'DESC'],3);
        
        if (!$programs) {
            throw $this->createNotFoundException(
                'No programs with category '.$categoryName.' title, found in program\'s table.'
            );
        }

        return $this->render('wild/category.html.twig', [
            'programs' => $programs,
            'categoryName' => $categoryName,
        ]);
    }

    /**
    * Getting a program
    *
    * @param string $slug The slugger
    * @Route("/program/{slug<^[a-z0-9-]+$>}", defaults={"slug" = null}, name="program")
    * @return Response
    */

    public function  showByProgram(?string $slug): Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }

        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );

        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with ' . $slug . ' title, found in program\'s table.'
            );
        }

        $seasons = $program->getSeasons();

        return $this->render('wild/seasons.html.twig', [
            'seasons' => $seasons,
            'slug'  => $slug,
        ]);
    }

    /**
     *
     * @param int $id
     * @Route("wild/program/seasons/{id}", defaults={"id" = null}, name="episodes")
     * @return Response
     */
    public function showBySeason(int $id) : Response
    {
        if (!$id) {
            throw $this
                ->createNotFoundException('No id has been sent to find the season\'s episodes.');
        }

        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findOneBy(['id' => ($id)]);

        $program = $season->getProgram();
        $episodes = $season->getEpisodes();

        return $this->render('wild/episodes.html.twig', [
            'season' => $season,
            'episodes'  => $episodes,
            'program' => $program,
        ]);
    }

}