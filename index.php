<?php

require_once __DIR__ . "/vendor/autoload.php";


// uso de las clases Request y Response de Symfony
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// uso de las clases para crear un esquema en Doctrine
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;


// Crea la Aplicación
// ==================

$app = new Silex\Application();

// configura el manejo de sesiones
$app->register(new Silex\Provider\SessionServiceProvider());

// configurar Twig en la Aplicación
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));


// Configura la conexión a la base de datos
$app->register(
  new Silex\Provider\DoctrineServiceProvider(),
  array(
    'db.options' => array(
      'dbname' => 'Connect_db',
  	  'host' => 'localhost',
  	  'user' => 'root',
  	  'password' => '',
  	  'driver' => 'pdo_mysql',
      'charset'       => 'utf8',
      'driverOptions' => array(1002 => 'SET NAMES utf8',),
    ),
  )
);


//el URL inicia con "//"
// if ( substr($_SERVER['REQUEST_URI'], 0, -1) == '/') {
//     header('Location: '.$_SERVER['REQUEST_URI'].'index.php');
//     die;
// }


// == ejecuta pruebas sobre la base de datos

// http://doctrine-orm.readthedocs.io/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html
// http://doctrine-orm.readthedocs.io/projects/doctrine-dbal/en/latest/reference/query-builder.html
// http://doctrine-orm.readthedocs.io/projects/doctrine-dbal/en/latest/reference/transactions.html
// http://doctrine-orm.readthedocs.io/projects/doctrine-dbal/en/latest/reference/schema-manager.html

// muestra los mensajes de error

$app['debug'] = true;



$app->get('/', function() use($app) {


    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');
    $mensaje=$app['session']->get('mensaje');
    $preciomax=$app['db']->fetchAssoc('select MAX(precioBoleta) as maximo from evento');


    return $app['twig']->render('index.twig.html',array('suser'=>$suser,'sstade'=>$sstade,'mensaje'=>$mensaje,'max'=>$preciomax));

})->bind('home');




$app ->post('/dologin', function (Request $request) use($app){

        $usuario=$request->get('usuario');
        $contrasena=$request->get('contrasena');
        


       $userlog = $app['db']->fetchAssoc('SELECT * FROM usuario WHERE login =? and password =?',array($usuario,$contrasena));

        if($userlog){
            $app['session']->set('userlog',$userlog);
            $app['session']->set('stadelog',true);
            $app['session']->set('mensaje',NULL);
            return $app->redirect($app['url_generator']->generate('home'));

        }else{
            $app['session']->set('mensaje',"usuario y contraseña no coinciden");
            return $app->redirect($app['url_generator']->generate('home'));
        }  

})->bind('dologin');








$app->post('/buscar-evento', function(Request $request) use($app) {


    $mensaje=null;
    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');

    $busqueda=$request->get('busqueda');
    $money=$request->get('money');
    $estrella=$request->get('estrellas');
    $preciomax=$app['db']->fetchAssoc('select MAX(precioBoleta) as maximo from evento');
    

    if($money==0&&$busqueda==null&&$estrella==null){
        
        $mensaje="Estos son todos nuestros eventos disponibles";
        $eventos = $app['db']->fetchAll('SELECT * FROM evento ');
    }
    elseif ($money!=0&&$busqueda!=null&&$estrella!=null) {

        $eventos = $app['db']->fetchAll('SELECT * FROM evento where (nombre=? or artista =?) and precioBoleta <= ?  and  estrella= ?  ',array($busqueda,$busqueda,$money,$estrella));
        $mensaje="Resultado de la busqueda";
        
        if($eventos==null){

            $mensaje="no encontramos eventos :(  Pero tal ves te interen estos eventos";
            $errodb=$app['session']->get('mensaje_bus');
            $eventos = $app['db']->fetchAll('SELECT * FROM evento ');

        }
    }
    elseif($busqueda==null&&$money!=0&&$estrella==null){

        $eventos = $app['db']->fetchAll('SELECT * FROM evento where precioBoleta <= ?  ',array($money));
        
        $mensaje="Resultado de la busqueda";

         if($eventos==null){

            $mensaje="no encontramos eventos con ese presupuesto , tal vez te interesen estos eventos";
            $eventos = $app['db']->fetchAll('SELECT * FROM evento');

        }

    }
    elseif($busqueda==null&&$money==0&&$estrella!=null){
        
        $mensaje="resultados de la busqueda";    
        $eventos = $app['db']->fetchAll('SELECT * FROM evento where estrella= ?',array($estrella));
        
        if($eventos==null){

            $mensaje="no encontramos eventos con esa calificacion , tal vez te interesen estos eventos";
            $eventos = $app['db']->fetchAll('SELECT * FROM evento');

        }
        
    }

  return $app['twig']->render('busqueda.twig.html',array('suser'=>$suser,'sstade'=>$sstade,'eventos'=>$eventos,'mensaje'=>$mensaje,'max'=>$preciomax));

})->bind('buscar');








$app->get('/buscardor', function() use($app) {


    $mensaje=null;
    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');
    $preciomax=$app['db']->fetchAssoc('select MAX(precioBoleta) as maximo from evento');

    $eventos = $app['db']->fetchAll('SELECT * FROM evento');

   return $app['twig']->render('busqueda.twig.html',array('suser'=>$suser,'sstade'=>$sstade,'eventos'=>$eventos,'mensaje'=>"",'max'=>$preciomax));

})->bind('buscador');


$app->get('/tipos', function() use($app) {


    $mensaje=null;
    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');

    $tipos = $app['db']->fetchAll('SELECT * FROM tipo');

   return $app['twig']->render('tipo.twig.html',array('suser'=>$suser,'sstade'=>$sstade,'tipos'=>$tipos));

})->bind('tipos');



$app -> get('/editartipo/{id}' , function ($id)  use($app){

    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');

    $tipo = $app['db']->fetchAssoc('SELECT * FROM tipo where id = ? ',array($id));

    return $app['twig']->render('editartipo.twig.html',array('suser'=>$suser,'sstade'=>$sstade,'tipo'=>$tipo,'mod'=>'1'));


} );







$app -> get('/evento/{id}' , function ($id)  use($app){

    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');

    $evento = $app['db']->fetchAssoc('SELECT * FROM evento where id = ? ',array($id));
    $comentarios=$app['db']->fetchAll('SELECT * FROM comentario where evento_id= ?',array($id));
    $id=null;

    return $app['twig']->render('evento.twig.html',array('suser'=>$suser,'sstade'=>$sstade,'evento'=>$evento,'comentarios'=>$comentarios));


} );







$app -> get('/nuevoevento' , function ()  use($app){

    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');

    $tipos=$app['db']->fetchAll('SELECT * FROM tipo ');

    return $app['twig']->render('editarevento.twig.html',array('suser'=>$suser,'sstade'=>$sstade,'tipos'=>$tipos,'mod'=>null));


})->bind('nuevo');





$app -> get('/nuevotipo' , function ()  use($app){

    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');

    return $app['twig']->render('editartipo.twig.html',array('suser'=>$suser,'sstade'=>$sstade,'mod'=>null));


})->bind('nuevotipo');






$app->post('/doevento', function(Request $request) use($app) {


    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');
    
    $case=$request->get('caso');
    
    $id=$request->get('id');
    $nombre=$request->get('nombre');
    $artista=$request->get('artista');
    $fecha=$request->get('fecha_evento');
    $precio=$request->get('precio');
    $longitud=$request->get('longitud');
    $latitud=$request->get('latitud');
    $estrella=$request->get('estrellas');
    
    $tipo=$request->get('tiponombre');
    
    $tipoid=$app['db']->fetchAssoc('SELECT * FROM tipo where nombre = ?',array($tipo));
    
    
    if($case==1){
        
        /* Case==1 para modificar tipos  */
       
        if($id!=null){
            
            $app['db']->update('tipo',array(
                'nombre' =>$nombre),
                array('id'=>$id)
            );
            
            
            
            
        }else{
            
               /* Case==0 para nuevos tipos  */
            
            $app['db']->insert('tipo', array(
                'nombre'=>$nombre));
        }
        
        return $app->redirect($app['url_generator']->generate('tipos'));
        
    }
    elseif($case==0){
          /* Case==1 Para entrar a eventos  */
        
    if($id!=null){
          /* ID definido  Para entrar edicion a eventos  */

            $app['db']->update('evento',
            //set value
            array('nombre' => $nombre,
                'artista' => $artista,
                'fecha_evento' => $fecha,
                'precioBoleta' => $precio,
                'longitud' => $longitud,
                'latitud' => $latitud,
                'estrella'=>$estrella,
                'tipo_id' => $tipoid[id]),
            //where
            array('id' => $id)
            );
            
            return $app->redirect($app['url_generator']->generate('buscador'));

    }
    else{
        
            /* ID  no definido  Para entrar nuevo a eventos  */
            $app['db']->insert('evento', array(
                'nombre' => $nombre,
                'artista' =>$artista,
                'fecha_evento' =>$fecha,
                'precioBoleta' =>$precio,
                'longitud' =>$longitud,
                'latitud' =>$latitud,
                'estrella'=>$estrella,
                'tipo_id'=>$tipoid[id]));
            }
            
        return $app->redirect($app['url_generator']->generate('buscador'));
            
    }


})->bind('grabar');




$app -> get('/editar/{id}' , function ($id)  use($app){

    $suser=$app['session']->get('userlog');
    $sstade=$app['session']->get('stadelog');

    $evento = $app['db']->fetchAssoc('SELECT * FROM evento where id = ? ',array($id));
    $tipos=$app['db']->fetchAll('SELECT * FROM tipo ');
    $mod=1;
    return $app['twig']->render('editarevento.twig.html',array('suser'=>$suser,'sstade'=>$sstade,'evento'=>$evento,'tipos'=>$tipos,'mod'=>$mod));

} );



$app -> get('/eliminar/{id}' , function ($id)  use($app){

    $app['db']->delete('evento', array(
    'id' => $id
    ));

    return $app->redirect($app['url_generator']->generate('home'));

} );





$app ->post('/grabarcomentario', function (Request $request) use($app){

        $suser=$app['session']->get('userlog');
        $sstade=$app['session']->get('stadelog');

        $comentario=$request->get('comentario');
        $idevento=$request->get('idevento');
        
         $app['db']->insert('comentario', array(
        'comentario' => $comentario,
        'usuario_login' =>$suser[login],
        'evento_id' =>$idevento));
        
     return $app->redirect($app['url_generator']->generate('home'));    

})->bind('comentario');




$app->get('/logout', function() use($app) {


        $app['session']->set('userlog',NULL);
        $app['session']->set('stadelog',NULL);

       return $app->redirect($app['url_generator']->generate('home'));


})->bind('salir');





$app->run();