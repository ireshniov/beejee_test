<?php

namespace App\Controller;

use App\Entity\Task;
use App\Exception\NotFoundException;
use App\Form\Type\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class TaskController
 * @package App\Controller
 */
class TaskController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Request $request
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function index(Request $request): string
    {
        $page = $request->query->get('page', 1);

        $sortBy = $request->query->get('sort_by', 'id');
        $sortDirection = $request->query->get('sort_direction', 'ASC');

        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('entity_manager');

        /** @var TaskRepository $taskRepository */
        $taskRepository = $entityManager->getRepository(Task::class);

        $itemsPerPage = $this->container->getParameter('task.items_per_page');

        //TODO implement paginator logic in service;
        $paginator = $taskRepository->getPaginator($page, $itemsPerPage, $sortBy, $sortDirection);

        if ($paginator->getIterator()->count() == 0){
            throw new NotFoundException();
        }

        $pageCount = ceil($paginator->count()/$itemsPerPage);

        /** @var Environment $twigEnvironment */
        $twigEnvironment = $this->container->get('twig');

        return $twigEnvironment->render('task/index.html.twig', [
            'tasks' => $paginator->getIterator()->getArrayCopy(),

            'pageCount' => $pageCount,
            'currentPage' => $page,
        ]);
    }

    /**
     * @param Request $request
     * @return string|RedirectResponse
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(Request $request)
    {
        // just setup a fresh $task object (remove the example data)
        $task = new Task();

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form_factory');

        //TODO: http://qaru.site/questions/16107869/symfony-form-failed-to-start-the-session-already-started-by-php
        // https://symfony.com/doc/current/components/http_foundation/session_php_bridge.html
        // https://symfony.com/doc/current/session/php_bridge.html
        $form = $formFactory->createBuilder(TaskType::class, $task, ['csrf_protection' => true])->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Task $task */
            $task = $form->getData();

            /** @var EntityManager $entityManager */
            $entityManager = $this->container->get('entity_manager');

            $entityManager->persist($task);
            $entityManager->flush();

            /** @var UrlGenerator $urlGenerator */
            $urlGenerator = $this->container->get('url_generator');

            $url = $urlGenerator->generate('task.list', [
                'page' => 1
            ]);

            /** @var Session $session */
            $session = $request->getSession();
            $session->getFlashBag()->add('task.created', sprintf("Task for user %s was successfully created", $task->getUserName()));

            return new RedirectResponse($url);
        }

        /** @var Environment $twigEnvironment */
        $twigEnvironment = $this->container->get('twig');

        return $twigEnvironment->render('task/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}