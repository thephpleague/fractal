import sys, os
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer

sys.path.append(os.path.abspath('_exts'))

extensions = []
master_doc = 'index'
highlight_language = 'php'

project = u'Silex'
copyright = u'2010 Fabien Potencier'

version = '0'
release = '0.0.0'

lexers['php'] = PhpLexer(startinline=True)
