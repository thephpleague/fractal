Contributing
============

We are open to contributions to the Silex code. If you find
a bug or want to contribute a provider, just follow these
steps.

* Fork `the Silex repository <https://github.com/fabpot/Silex>`_
  on github.

* Make your feature addition or bug fix.

* Add tests for it. This is important so we don't break it in a future version unintentionally.

* Optionally, add some technical documentation.

* `Send a pull request <https://help.github.com/articles/creating-a-pull-request>`_, to the correct `target branch`_. 
  Bonus points for topic branches.

If you have a big change or would like to discuss something,
please join us on the `mailing list
<http://groups.google.com/group/silex-php>`_.

.. note::

    Any code you contribute must be licensed under the MIT
    License.

Target branch
=============

Before you create a pull request for Silex, you need to determine which branch
to submit it to. Read this section carefully first.

Silex has two active branches: `1.0` and `master` (`1.1`).

* **1.0**: Bugfixes and documentation fixes go into the 1.0 branch. 1.0 is
  periodically merged into master. The 1.0 branch targets versions 2.1, 2.2 and
  2.3 of Symfony2.

* **1.1**: All new features go into the 1.1 branch. Changes cannot break
  backward compatibility. The 1.1 branch targets the 2.3 version of Symfony2.


Writing Documentation
=====================

The documentation is written in `reStructuredText
<http://docutils.sourceforge.net/rst.html>`_ and can be generated using `sphinx
<http://sphinx-doc.org>`_.

.. code-block:: bash

    $ cd doc
    $ sphinx-build -b html . build
