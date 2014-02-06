Cookbook
========

The cookbook section contains recipes for solving specific problems.

.. toctree::
    :maxdepth: 1
    :hidden:

    json_request_body
    translating_validation_messages
    session_storage
    form_no_csrf
    validator_yaml
    sub_requests
    error_handler
    multiple_loggers

Recipes
-------

* :doc:`Accepting a JSON request body <json_request_body>` A common need when
  building a restful API is the ability to accept a JSON encoded entity from
  the request body.

* :doc:`Translating Validation Messages<translating_validation_messages>`.

* :doc:`How to use PdoSessionStorage to store sessions in the database
  <session_storage>`.

* :doc:`How to disable the CSRF Protection on a form using the FormExtension
  <form_no_csrf>`.

* :doc:`How to use YAML to configure validation <validator_yaml>`.

* :doc:`How to make sub-requests <sub_requests>`.

* :doc:`How to convert errors to exceptions <error_handler>`.

* :doc:`How to use multiple monolog loggers <multiple_loggers>`.
