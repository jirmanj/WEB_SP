<?php
/** For more info about namespaces plase @see http://php.net/manual/en/language.namespaces.importing.php */
namespace Mini\Core;

class Application
{
  //  private $urls = array("home");
    /** @var null The controller */
    private $url_controller = null;

    /** @var null The method (of the above controller), often also named "action" */
    private $url_action = null;

    /** @var array URL parameters */
    private $url_params = array();

    /**
     * "Start" the application:
     * Analyze the URL elements and calls the according controller/method or the fallback
     */
    public function __construct()
    {
        // create array with URL parts in $url
        $this->splitUrl();

        // check for controller: no controller given ? then load start-page
        //  echo print_r($_SERVER['']);
        //   die;
        if (!$this->url_controller) {

            $page = new \Mini\Controller\HomeController();
            $page->index();

        } elseif (file_exists(APP . 'Controller/' . ucfirst($this->url_controller) . 'Controller.php')) {
            // here we did check for controller: does such a controller exist ?

            // if so, then load this file and create this controller
            // like \Mini\Controller\CarController
            $controller = "\\Mini\\Controller\\" . ucfirst($this->url_controller) . 'Controller';
            $this->url_controller = new $controller();

            // check for method: does such a method exist in the controller ?
            if (method_exists($this->url_controller, $this->url_action)) {

                if (!empty($this->url_params)) {
                    // Call the method and pass arguments to it
                    call_user_func_array(array($this->url_controller, $this->url_action),array($this->url_params));
                } else {
                    // If no parameters are given, just call the method without parameters, like $this->home->method();
                    $this->url_controller->{$this->url_action}();
                }

            } else {
                if (strlen($this->url_action) == 0) {
                    // no action defined: call the default index() method of a selected controller
                    $this->url_controller->index();
                } else {
                    header('location: ' . URL . 'index.php?page=error');
                }
            }
        } else {
            header('location: ' . URL . 'index.php?page=error');
        }
    }

    /**
     * Get and split the URL
     */
    private function splitUrl()
    {
        if (isset($_SERVER['PATH_INFO'])) {
            $this->url_controller = "neexistuje";
        } else {
            if (strlen($_SERVER["QUERY_STRING"]) > 0) {
                if (isset($_REQUEST['page']) && strlen($_REQUEST['page'])>0) {
                    $this->url_controller = $_REQUEST['page'];
                }else{
                    $this->url_controller = "neexistuje";
                }
            }
            if (isset($_REQUEST['action'])) {
                $this->url_action = $_REQUEST['action'];
            }
            if (isset($_REQUEST['param'])) {
                $this->url_params = $_REQUEST['param'];
            }
        }
    }










        /*if(isset($_REQUEST['page'])){
        $this->url_action = $_REQUEST['page'];
        }
        if(isset($_REQUEST['action'])){
            $this->url_action = $_REQUEST['action'];
        }
        if(isset($_REQUEST['param'])){
            $this->url_params = $_REQUEST['param'];
        }


    } */





















       /* if (isset($_SERVER['PATH_INFO'])) {
            echo "PATH";
            $this->url_controller = null;
        } else {
            if (strlen($_SERVER["QUERY_STRING"]) > 0) {
                echo "QUERY    ";
                $url = $_SERVER["QUERY_STRING"];
                if (strpos($url, "page=")) {
                    echo "ano (y)";
                    foreach ($this->urls as $value) {
                        if (strpos($_REQUEST['page'], $value)) {
                            echo "ano";
                            $this->url_controller = $value;
                            break;
                        }else{
                            $this->url_controller = null;
                        }
                    }
                } else {
                    $this->url_controller = null;
                }
                if (isset($_REQUEST['action'])) {
                    $this->url_action = $_REQUEST['action'];
                }
                if (isset($_REQUEST['param'])) {
                    $this->url_params = $_REQUEST['param'];
                }
            }
        }
    } */


    /*     >0 || strlen($_SERVER["QUERY_STRING"])>0) {
         echo "ANO";
         echo "<br>";
         echo $_SERVER['PATH_INFO'];
         echo "<br>";
         echo $_SERVER["QUERY_STRING"];
         echo "<br>";
     }

 }

 /*    if(isset($_REQUEST['page'])){
         $this->url_controller = $_REQUEST['page'];
     }else{
         if($_SERVER["QUERY_STRING"] == null) {
                 $this->url_controller = null;
         }else{
             if(strpos($_SERVER["QUERY_STRING"], 'page=')){
                 echo $_SERVER["QUERY_STRING"];
                 echo "ANO";
                 $this->url_controller = "home";
             }else{
                 echo $_SERVER["QUERY_STRING"];
                 echo "NE";
                 $this->url_controller = "error";
             }
         }
     }
     if(isset($_REQUEST['action'])){
         $this->url_action = $_REQUEST['action'];
     }
     if(isset($_REQUEST['param'])){
         $this->url_params = $_REQUEST['param'];
     } */

}
