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
     * @param int $id
     * @param Request $request
     * @return string|RedirectResponse
     * @throws LoaderError
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function update(int $id, Request $request)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('entity_manager');

        /** @var TaskRepository $taskRepository */
        $taskRepository = $entityManager->getRepository(Task::class);

        $task = $taskRepository->find($id);

        if (!$task instanceof Task) {
            throw new NotFoundException();
        }

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->container->get('url_generator');

        $url = $urlGenerator->generate('task.list');

        $response = new RedirectResponse($url);

        if ($task->isCompleted()) {
            return $response;
        }

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form_factory');

        $form = $formFactory->createBuilder(TaskType::class, $task)->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $response;
        }

        /** @var Environment $twigEnvironment */
        $twigEnvironment = $this->container->get('twig');

        return $twigEnvironment->render('task/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function complete(int $id, Request $request)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('entity_manager');

        /** @var TaskRepository $taskRepository */
        $taskRepository = $entityManager->getRepository(Task::class);

        $task = $taskRepository->find($id);

        if (!$task instanceof Task) {
            throw new NotFoundException();
        }

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->container->get('url_generator');

        $redirectUrl = $request->headers->get('referer')?
            $request->headers->get('referer'): $urlGenerator->generate('task.list');


        $response = new RedirectResponse($redirectUrl);

        if ($task->isCompleted()) {
            return $response;
        }

        $task->setIsCompleted(true);

        $entityManager->flush();

        return $response;
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
        $task = new Task();

        /** @var FormFactory $formFactory */
        $formFactory = $this->container->get('form_factory');

        $form = $formFactory->createBuilder(TaskType::class, $task)->getForm();

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

            $url = $urlGenerator->generate('task.list');

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