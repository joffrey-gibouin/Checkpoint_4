<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IngredientController extends AbstractController
{
    /**
     * @Route("/mes_ingredient", name="all_ingredient")
     */
    public function showAllIngredient(IngredientRepository $ingredientRepository): Response
    {
        $ingredients = $ingredientRepository->findAll();
        return $this->render('ingredient/index.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }

    /**
     * @Route("/ajouter_un_ingredient", name="new_ingredient")
     */
    public function new(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $entityManager = $managerRegistry->getManager();
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $entityManager->persist($ingredient);
            $entityManager->flush();

            $name = $ingredient->getName();
            $this->addFlash(
                'success',
                'L\'ingredient a '. $name .' bien été ajouté.'
            );
            return $this->redirectToRoute('all_ingredient');
        }

        return $this->renderForm('ingredient/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/modifier_un_ingredient/{id}", name="edit_ingredient")
     */
    public function edit(Request $request, Ingredient $ingredient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $name = $ingredient->getName();
            $this->addFlash(
                'success',
                'L\'ingredient ' . $name . ' a bien été modifié!'
            );
            return $this->redirectToRoute('all_ingredient', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('ingredient/edit.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/supprimer_un_ingredient/{id}", name="delete_ingredient")
     */
    public function delete(ManagerRegistry $managerRegistry, int $id): Response
    {
        $entityManager = $managerRegistry->getManager();
        $ingredient = $entityManager->getRepository(Ingredient::class)->find($id);

        if (!$ingredient) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $entityManager->remove($ingredient);
        $entityManager->flush();

        $name = $ingredient->getName();
        $this->addFlash(
            'danger',
            'L\'ingredient ' . $name . ' a bien été supprimé!'
        );

        return $this->redirectToRoute('all_ingredient', [
            'id' => $ingredient->getId()
        ]);
    }
}
