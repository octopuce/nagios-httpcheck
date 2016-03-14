<?php

use Silex\Provider\FormServiceProvider;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Octopuce\Nagios\Httpcheck\DefaultDataAccessor;
use Octopuce\Nagios\Httpcheck\Service as HttpcheckService;

try {
    
    define("APP_PATH", realpath(__DIR__ . "/../"));
    
    require_once APP_PATH."/bootstrap.php";
    
// It should bootstrap the Silex app 
    $app = new Silex\Application();

// It should bootstrap the app DB
    $app["db"] = $db;

// It should bootstrap twig
    $app->register(new Silex\Provider\TwigServiceProvider(), array(
        'twig.path' => APP_PATH . '/twig/views',
    ));
    $app->before(function () use ($app) {
        $app['twig']->addGlobal('layout', null);
        $app['twig']->addGlobal('layout', $app['twig']->loadTemplate('layout.html.twig'));
    });

// It should bootstrap the Form Provider

    $app->register(new FormServiceProvider());
    $app->register(new Silex\Provider\TranslationServiceProvider());


    $app->register(new Silex\Provider\ValidatorServiceProvider());
    $app->register(new Silex\Provider\TranslationServiceProvider(), array(
        'translator.domains' => array(),
    ));

    /**
     * @var Symfony\Component\Form\Form
     */
    $form = $app['form.factory']->createBuilder('form')
            ->add("id", "hidden", array(
            ))
            ->add("fqdn", "text", array(
                'constraints' => array(new Assert\NotNull),
                "label" => "FQDN"
            ))
            ->add("ip", "text", array(
                'constraints' => array(new Assert\Ip(array("version" => Assert\Ip::ALL)))
            ))
            ->add("host", "text", array(
                'constraints' => array(new Assert\NotNull)
            ))
            ->add("uri", "text", array(
                'constraints' => array(new Assert\NotNull)
            ))
            ->add("ssl", "choice", array(
                'choices' => array(1 => 'yes', 0 => 'no'),
                'expanded' => true,
                'constraints' => new Assert\Choice(array(0, 1)),
            ))
            ->add("no_alert", "choice", array(
                'choices' => array(1 => 'yes', 0 => 'no'),
                'expanded' => true,
                'constraints' => new Assert\Choice(array(0, 1)),
            ))
            ->add("port", "text", array(
                "required" => false,
            ))
            ->add("status", "text", array(
                "required" => false,
                'constraints' => array(
                    new Assert\GreaterThanOrEqual(200),
                )
            ))
            ->add("regexp", "text", array(
                "required" => false,
            ))
            ->add("invert_regexp", "choice", array(
                "required" => false,
                'choices' => array(1 => 'yes', 0 => 'no'),
                'expanded' => true,
                'constraints' => new Assert\Choice(array(0, 1)),
            ))
            ->add("login", "text", array(
                "required" => false,
            ))
            ->add("pass", "text", array(
                "required" => false,
            ))
            ->getForm();


// Use the library DefaultDataProvider
    $dataProvider = new DefaultDataAccessor($db);

// Register the Nagios Httpcheck Service
    $nagiosHttpcheckService = new HttpcheckService($dataProvider);
    $app["nagiosHttpcheckService"] = $nagiosHttpcheckService;

// It should set a debug flag if file exists
    if (is_file(APP_PATH . "/debug")) {
        $app["debug"] = true;
    } 


// It should have a GET default route
    $app->get('/', function (Silex\Application $app) {
        return $app->redirect('/index');
    });

// It should have a GET route for listing checks
    $app->get('/index', function ( Request $request ) use ( $app, $nagiosHttpcheckService) {

        try {
            $checkList = $nagiosHttpcheckService->getHttpcheckList($request); // filter through parameters
        } catch (\Exception $e) {
            echo $e->getTraceAsString();
        }

        return $app['twig']->render('index.html.twig', array(
                    'httpcheckList' => $checkList,
        ));
    });

// It should have a GET route for retrieving all checks
    $app->get('/all', function (Silex\Application $app) use ($nagiosHttpcheckService) {

        $result = $nagiosHttpcheckService->getAllForNagios();
        if( false === $result ){
            return $app->abort(404, 'Failed to retrieve the check list.');
        }
        return $result;
    });

// It should have a GET route for retrieving only critical checks
    $app->get('/alert', function (Silex\Application $app) use ($nagiosHttpcheckService) {

        $result = $nagiosHttpcheckService->getAllForNagios(true);
        if( false === $result ){
            return $app->abort(404, 'Failed to retrieve the check list.');
        }
        return $result;
    });


// It should have a GET route for creating checks
    $app->get('/add', function ( ) use ($app, $form) {
        // some default data for when the form is displayed the first time
        $data = array(
            'ssl' => false,
            'no_alert' => true,
            'status' => 200
        );
        $form->setData($data);
        return $app['twig']->render('form.html.twig', array(
                    'form' => $form->createView(),
                    "form_type" => "horizontal"
        ));
    });

// It should have a GET route for creating checks
    $app->get('/copy/{id}', function ( $id ) use ($app, $form,$nagiosHttpcheckService) {

        // It should verify that the check exists
        $data = $nagiosHttpcheckService->findHttpcheck($id);

        if ($data) {
            unset($data["id"]);
            // It should show the form
            $form->setData($data);
            return $app['twig']->render('form.html.twig', array(
                        'form' => $form->createView(),
            ));
        } else {
            return $app['twig']->render('error.html.twig', array(
                        'errorList' => array("Invalid check ID")
            ));
        }
    });


// It should have a GET route for editing checks
    $app->get('/edit/{id}', function (Request $request, $id) use( $app, $form, $nagiosHttpcheckService ) {

        // It should verify that the check exists
        $check = $nagiosHttpcheckService->findHttpcheck($id);
        if ($check) {

            // It should show the form
            $form->setData($check);
            return $app['twig']->render('form.html.twig', array(
                        'form' => $form->createView(),
            ));
        } else {
            return $app['twig']->render('error.html.twig', array(
                        'errorList' => array("Invalid check ID")
            ));
        }
    });

// It should have a POST route for saving checks  
    $app->post('/save', function ( Request $request ) use ($app, $form, $nagiosHttpcheckService) {

        // It should use an errorList array
        $errorList = array();

        // It should let the form check the request
        $form->handleRequest($request);

        // It should try to update the httpcheck record
        if ($form->isValid()) {

            $data = $form->getData();
            if ($nagiosHttpcheckService->saveHttpcheck($data)) {
                // redirect somewhere
                return $app->redirect('/index');
            }

            $errorList[] = "Failed to save new check !";
        }
        return $app['twig']->render('form.html.twig', array(
                    'errorList' => $errorList,
                    'form' => $form->createView()
        ));
    });

// It should have a GET route for deleting checks  
    $app->get('/delete/{id}', function ($id) use ($app, $nagiosHttpcheckService) {

        // It should request the check deletion
        $result = $nagiosHttpcheckService->deleteCheck($id);

        if (!$result) {

            return $app['twig']->render('error.html.twig', array(
                        'errorList' => array("Failed to delete from database")
            ));
        }
        // Redirect to list
        return $app->redirect('/index');
    });

// It should handle errors 
    $app->error(function (\Exception $e, $code) {
        return new Response('# Error: '.$e->getMessage());
    });
    
// It should handle views
    $app->view(function (array $controllerResult, Request $request) use ($app) {
        return $controllerResult;
    });

    // Main();
    $app->run();
    
} catch (Exception $e) {
    var_dump($e);
}