<?php
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  $app = new \Slim\App;

  // add routes below
  $app->options('/{routes:.+}', function ($request, $response, $args) {
      return $response;
  });

  $app->add(function ($req, $res, $next) {
      $response = $next($req, $res);
      return $response
              ->withHeader('Access-Control-Allow-Origin', '*')
              ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
              ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  });
  /**************** User Routes ****************/

  // add User
  $app->post('/api/user', function(Request $request, Response $response){
    $first_name = $request->getParam('first_name');
    $last_name = $request->getParam('last_name');
    $username = $request->getParam('username');
    $password = $request->getParam('password');
    $email = $request->getParam('email');
    $active = $request->getParam('active');
    $role_id = $request->getParam('role_id');

    $sql = "INSERT INTO Users (first_name,last_name,username,password,email,active,role_id) VALUES
    (:first_name,:last_name,:username,:password,:email,:active,:role_id)";

    try{
      // Get DB Object
      $db = new db();

      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':first_name', $first_name);
      $stmt->bindParam(':last_name', $last_name);
      $stmt->bindParam(':username', $username);
      $stmt->bindParam(':password', $password);
      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':active', $active);
      $stmt->bindParam(':role_id', $role_id);

      $stmt->execute();
      echo '{"notice":{"text":"User added"}}';
      $db = null;
    } catch(PDOException $e){
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // get User
  $app->get('/api/user/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql = "SELECT user_id, first_name, last_name, username, email, date_created, active, role_id
            FROM Users
            WHERE user_id = $id";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->query($sql);
      $user = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;
      echo json_encode($user);

    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // get users
  $app->get('/api/users', function(Request $request, Response $response){
    $sql = "SELECT Users.user_id, Users.first_name, Users.last_name,
                   Users.username, Users.email, Users.date_created,
                   Users.active, Users.role_id, Roles.role
            FROM Users
            LEFT JOIN Roles ON Users.role_id = Roles.role_id";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->query($sql);
      $users = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;
      echo json_encode($users);

    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // get usernames
  $app->get('/api/users/usernames', function(Request $request, Response $response){
    $sql = "SELECT username FROM Users";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->query($sql);
      $usernames = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;
      echo json_encode($usernames);
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // delete users
  $app->delete('/api/user/delete/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql = "UPDATE Users SET
            deleted = 1
            WHERE user_id = :id";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':id', $id);
      $stmt->execute();
      $db = null;

      echo '{"notice": {"text": "User Deleted"}}';
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // edit users
  $app->put('/api/user/update/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $first_name = $request->getParam('first_name');
    $last_name = $request->getParam('last_name');
    $username = $request->getParam('username');
    $password = $request->getParam('password');
    $email = $request->getParam('email');
    $active = $request->getParam('active');
    $role_id = $request->getParam('role_id');

    $sql = "UPDATE Users SET
              first_name = :first_name,
              last_name  = :last_name,
              username   = :username,
              password   = :password,
              email      = :email,
              active     = :active,
              role_id    = :role_id
            WHERE user_id = $id";

    try {
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':first_name', $first_name);
      $stmt->bindParam(':last_name', $last_name);
      $stmt->bindParam(':username', $username);
      $stmt->bindParam(':password', $password);
      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':active', $active);
      $stmt->bindParam(':role_id', $role_id);

      $stmt->execute();

      echo '{"notice": {"text": "User Updated"}}';
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // Login
  $app->post('/api/login', function(Request $request, Response $response){
    $username = $request->getParam('username');

    $sql = "SELECT username, password FROM Users WHERE username = :username";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':username', $username);
      $stmt->execute();
      $userCred = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      echo json_encode($userCred);

    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  /**************** Role Routes ****************/

  // Get roles
  $app->get('/api/roles', function(Request $request, Response $response){
    $sql = "SELECT * FROM Roles";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->execute();
      $roles = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      echo json_encode($roles);
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // add role
  $app->post('/api/role', function(Request $request, Response $response){
    $role = $request->getParam('role');

    $sql = "INSERT INTO Roles (role) VALUES (:role)";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':role', $role);
      $stmt->execute();

      echo '{"notice": {"text": "role added"}}';
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  /**************** Post Routes ****************/
  // add post
  $app->post('/api/post', function(Request $request, Response $response){
    $maxLength = 4000;
    $post_title = $request->getParam('post_title');
    $post_author = $request->getParam('post_author');
    $post_text = $request->getParam('post_text');

    $segmentText = str_split($post_text, $maxLength);

    $sqlPost = "INSERT INTO Posts (post_title, post_author)
                VALUES (:post_title, :post_author)";
    $sqlPostDetails = "INSERT INTO PostDetails (post_id, sequence, post_text)
                       VALUES (:post_id, :sequence, :post_text)";
    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      // add post info
      $stmt = $db->prepare($sqlPost);
      $stmt->bindParam(':post_title', $post_title);
      $stmt->bindParam(':post_author', $post_author);

      $stmt->execute();

      $stmt = null;
      //add post detail
      $post_id = $db->lastInsertId();

      $segmentCount = count($segmentText);

      for($i = 0; $i < $segmentCount; $i++){
        $sequence = $i + 1;
        $stmt = $db->prepare($sqlPostDetails);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':sequence', $sequence);
        $stmt->bindParam(':post_text', $segmentText[$i]);
        $stmt->execute();
      }

      echo '{"notice": {"text": "post added successfully segment count = '. $segmentCount .'"}}';
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // get post list
  $app->get('/api/post/list', function(Request $request, Response $response){
    $sql = "SELECT Posts.post_id, Posts.post_title, Posts.post_date,
                   Posts.post_author, Users.username, Users.first_name,
                   Users.last_name
            FROM Posts
            LEFT JOIN Users ON Posts.post_author = Users.user_id
            WHERE deleted = 0";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->execute();
      $post_list = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      echo json_encode($post_list);

    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // get posts
  $app->get('/api/posts', function(Request $request, Response $response){
    $sql = "SELECT Posts.post_id, Posts.post_title, Posts.post_date,
                   Posts.post_author, Users.username, Users.first_name,
                   Users.last_name, PostDetails.sequence, PostDetails.post_text
            FROM Posts
            LEFT JOIN PostDetails ON Posts.post_id = PostDetails.post_id
            LEFT JOIN Users ON Posts.post_author = Users.user_id
            WHERE Posts.deleted = 0";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->execute();
      $postsRaw = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;
      //echo json_encode($posts[0]);
      $postsRawLength = count($postsRaw);

      $posts = array();
      $postsLength = 0;
      for($i = 0; $i < $postsRawLength; $i++){
        if($i > 0){
          if($postsRaw[$i - 1]->post_id === $postsRaw[$i]->post_id){
          $posts[$postsLength - 1]->post_content .= $postsRaw[$i]->post_text;
          } else {
          $post->id = $postsRaw[$i]->post_id;
          $post->title = $postsRaw[$i]->post_title;
          $post->post_date = $postsRaw[$i]->post_date;
          $post->author_id = $postsRaw[$i]->post_author;
          $post->author_username = $postsRaw[$i]->username;
          $post->author_first_name = $postsRaw[$i]->first_name;
          $post->author_last_name = $postsRaw[$i]->last_name;
          $post->post_content = $postsRaw[$i]->post_text;


          array_push($posts, $post);
          $post = null;
          $postsLength++;
          }
        } else {
          $post->id = $postsRaw[$i]->post_id;
          $post->title = $postsRaw[$i]->post_title;
          $post->post_date = $postsRaw[$i]->post_date;
          $post->author_id = $postsRaw[$i]->post_author;
          $post->author_username = $postsRaw[$i]->username;
          $post->author_first_name = $postsRaw[$i]->first_name;
          $post->author_last_name = $postsRaw[$i]->last_name;
          $post->post_content = $postsRaw[$i]->post_text;

          array_push($posts, $post);
          $post = null;
          $postsLength++;
        }
      }

      echo json_encode($posts);
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // get post
  $app->get('/api/post/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql = "SELECT Posts.post_id, Posts.post_title, Posts.post_date,
                   Posts.post_author, Users.username, Users.first_name,
                   Users.last_name, PostDetails.sequence, PostDetails.post_text
            FROM Posts
            LEFT JOIN PostDetails ON Posts.post_id = PostDetails.post_id
            LEFT JOIN Users ON Posts.post_author = Users.user_id
            WHERE Posts.post_id = :post_id";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':post_id', $id);
      $stmt->execute();
      $postRaw = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      $postRawLength = count($postRaw);

      $posts = array();
      $postsLength = 0;
      for($i = 0; $i < $postRawLength; $i++){
        if($i > 0){
          if($postRaw[$i - 1]->post_id === $postRaw[$i]->post_id){
            $post->post_text .= $postRaw[$i]->post_text;
          } else {
            $post->id = $postRaw[$i]->post_id;
            $post->title = $postRaw[$i]->post_title;
            $post->post_date = $postRaw[$i]->post_date;
            $post->author_id = $postRaw[$i]->post_author;
            $post->author_username = $postRaw[$i]->username;
            $post->author_first_name = $postRaw[$i]->first_name;
            $post->author_last_name = $postRaw[$i]->last_name;
            $post->post_content = $postRaw[$i]->post_text;
          }
        } else {
          $post->id = $postRaw[$i]->post_id;
          $post->title = $postRaw[$i]->post_title;
          $post->post_date = $postRaw[$i]->post_date;
          $post->author_id = $postRaw[$i]->post_author;
          $post->author_username = $postRaw[$i]->username;
          $post->author_first_name = $postRaw[$i]->first_name;
          $post->author_last_name = $postRaw[$i]->last_name;
          $post->post_content = $postRaw[$i]->post_text;
        }
      }

      echo json_encode($post);
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // get posts by user
  $app->get('/api/posts/user/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sql = "SELECT Posts.post_id, Posts.post_title, Posts.post_date,
                   Posts.post_author, Users.username, Users.first_name,
                   Users.last_name, PostDetails.sequence, PostDetails.post_text
            FROM Posts
            LEFT JOIN PostDetails ON Posts.post_id = PostDetails.post_id
            LEFT JOIN Users ON Posts.post_author = Users.user_id
            WHERE Posts.post_author = :post_author AND Posts.deleted = 0";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':post_author', $id);
      $stmt->execute();
      $postsRaw = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      $postsRawLength = count($postsRaw);

      $posts = array();
      $postsLength = 0;
      for($i = 0; $i < $postsRawLength; $i++){
        if($i > 0){
          if($postsRaw[$i - 1]->post_id === $postsRaw[$i]->post_id){
          $posts[$postsLength - 1]->post_text .= $postsRaw[$i]->post_text;
          } else {
            $post->id = $postsRaw[$i]->post_id;
            $post->title = $postsRaw[$i]->post_title;
            $post->post_date = $postsRaw[$i]->post_date;
            $post->author_id = $postsRaw[$i]->post_author;
            $post->author_username = $postsRaw[$i]->username;
            $post->author_first_name = $postsRaw[$i]->first_name;
            $post->author_last_name = $postsRaw[$i]->last_name;
            $post->post_content = $postsRaw[$i]->post_text;

          array_push($posts, $post);
          $post = null;
          $postsLength++;
          }
        } else {
          $post->id = $postsRaw[$i]->post_id;
          $post->title = $postsRaw[$i]->post_title;
          $post->post_date = $postsRaw[$i]->post_date;
          $post->author_id = $postsRaw[$i]->post_author;
          $post->author_username = $postsRaw[$i]->username;
          $post->author_first_name = $postsRaw[$i]->first_name;
          $post->author_last_name = $postsRaw[$i]->last_name;
          $post->post_content = $postsRaw[$i]->post_text;

          array_push($posts, $post);
          $post = null;
          $postsLength++;
        }
      }

      echo json_encode($posts);
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // get post comments
  $app->get('/api/post/{id}/comments', function(Request $request, Response $response){
    $post_id = $request->getAttribute('id');

    $sql = "SELECT Comments.comment_id, Comments.post_id, Comments.comment,
                   Comments.commenter_id, Comments.comment_date, Users.username,
                   Users.first_name, Users.last_name
            FROM Comments
            LEFT JOIN Users ON Comments.commenter_id = Users.user_id
            WHERE post_id = :post_id AND deleted = 0";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':post_id', $post_id);
      $stmt->execute();
      $comments = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      echo json_encode($comments);
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // delete post
  $app->delete('/api/post/{id}', function(Request $request, Response $Response){
    $post_id = $request->getAttribute('id');

    $sql = "UPDATE Posts SET
            deleted = 1
            WHERE post_id = :post_id";

    try{
      // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();
    $db = null;

    echo '{"notice": {"text": "post Deleted"}}';
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // edit post
  $app->put('/api/post/{id}/update', function(Request $request, Response $response){
    $post_id = $request->getAttribute('id');
    $maxLength = 4000;
    $post_title = $request->getParam('post_title');
    $post_author = $request->getParam('post_author');
    $post_text = $request->getParam('post_text');

    $segmentText = str_split($post_text, $maxLength);

    // update original post info
    $sqlPost = "UPDATE Posts SET
                  post_title = :post_title,
                  post_author = :post_author
                WHERE post_id = :post_id";

    // delete original post contents
    $sqlDeleteContents = "DELETE FROM PostDetails
                          WHERE post_id = :post_id";

    // insert new post contents
    $sqlPostDetails = "INSERT INTO PostDetails (post_id, sequence, post_text)
                       VALUES (:post_id, :sequence, :post_text)";
    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      // update post info
      $stmt = $db->prepare($sqlPost);
      $stmt->bindParam(':post_title', $post_title);
      $stmt->bindParam(':post_author', $post_author);
      $stmt->bindParam(':post_id', $post_id);

      $stmt->execute();

      $stmt = null;

      // remove old post contents

      $stmt = $db->prepare($sqlDeleteContents);
      $stmt->bindParam('post_id', $post_id);
      $stmt->execute();

      $stmt = null;
      //add post detail

      $segmentCount = count($segmentText);

      for($i = 0; $i < $segmentCount; $i++){
        $sequence = $i + 1;
        $stmt = $db->prepare($sqlPostDetails);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':sequence', $sequence);
        $stmt->bindParam(':post_text', $segmentText[$i]);
        $stmt->execute();
      }

      echo '{"notice": {"text": "post updated successfully"}}';
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  /************** Comment Routes ***************/

  // add comment
  $app->post('/api/post/{id}/comment', function(Request $request, Response $response){
    $post_id = $request->getAttribute('id');
    $comment = $request->getParam('comment');
    $commenter_id = $request->getParam('commenter_id');

    $sql = "INSERT INTO Comments (post_id, comment, commenter_id)
            VALUES (:post_id, :comment, :commenter_id)";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':post_id', $post_id);
      $stmt->bindParam(':comment', $comment);
      $stmt->bindParam(':commenter_id', $commenter_id);

      $stmt->execute();

      echo '{"notice": {"text": "Comment added successfully"}}';
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // delete comment
  $app->delete('/api/comment/{comment_id}', function(Request $request, Response $response){
    $comment_id = $request->getAttribute('comment_id');

    $sql = "UPDATE Comments SET
            deleted = 1
            WHERE comment_id = :comment_id";

    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':comment_id', $comment_id);
      $stmt->execute();
      echo '{"notice": {"text": "Comment deleted successfully"}}';
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }
  });

  // edit comment
  $app->put('/api/comment/{comment_id}', function(Request $request, Response $response){
    $comment_id = $request->getAttribute('comment_id');
    $commenter_id = $request->getParam('commenter_id');
    $comment = $request->getParam('comment');

    $sql = "UPDATE Comments SET
            comment = :comment,
            commenter_id = :commenter_id
            WHERE comment_id = :comment_id";

    try {
      //Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':comment', $comment);
      $stmt->bindParam(':commenter_id', $commenter_id);
      $stmt->bindParam(':comment_id', $comment_id);
      $stmt->execute();

      echo '{"notice": {"text": "Comment updated successfully"}}';
    } catch(PDOException $e) {
      echo '{"error": {"text": '.$e->getMessage().'}}';
    }

  });

?>
