Accepting a JSON request body
=============================

A common need when building a restful API is the ability to accept a JSON
encoded entity from the request body.

An example for such an API could be a blog post creation.

Example API
-----------

In this example we will create an API for creating a blog post. The following
is a spec of how we want it to work.

Request
~~~~~~~

In the request we send the data for the blog post as a JSON object. We also
indicate that using the ``Content-Type`` header:

.. code-block:: text

    POST /blog/posts
    Accept: application/json
    Content-Type: application/json
    Content-Length: 57

    {"title":"Hello World!","body":"This is my first post!"}

Response
~~~~~~~~

The server responds with a 201 status code, telling us that the post was
created. It tells us the ``Content-Type`` of the response, which is also
JSON:

.. code-block:: text

    HTTP/1.1 201 Created
    Content-Type: application/json
    Content-Length: 65
    Connection: close

    {"id":"1","title":"Hello World!","body":"This is my first post!"}

Parsing the request body
------------------------

The request body should only be parsed as JSON if the ``Content-Type`` header
begins with ``application/json``. Since we want to do this for every request,
the easiest solution is to use an application before middleware.

We simply use ``json_decode`` to parse the content of the request and then
replace the request data on the ``$request`` object::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\ParameterBag;

    $app->before(function (Request $request) {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
    });

Controller implementation
-------------------------

Our controller will create a new blog post from the data provided and will
return the post object, including its ``id``, as JSON::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    $app->post('/blog/posts', function (Request $request) use ($app) {
        $post = array(
            'title' => $request->request->get('title'),
            'body'  => $request->request->get('body'),
        );

        $post['id'] = createPost($post);

        return $app->json($post, 201);
    });

Manual testing
--------------

In order to manually test our API, we can use the ``curl`` command line
utility, which allows sending HTTP requests:

.. code-block:: bash

    $ curl http://blog.lo/blog/posts -d '{"title":"Hello World!","body":"This is my first post!"}' -H 'Content-Type: application/json'
    {"id":"1","title":"Hello World!","body":"This is my first post!"}
