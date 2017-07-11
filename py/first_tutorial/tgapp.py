# -*- coding: utf-8 -*-

__author__ = 'steves'

from tg import expose, TGController, AppConfig
from wsgiref.simple_server import make_server
# import webhelpers2
import webhelpers2.text



class RootController(TGController):
    @expose()
    def index(self):
        return 'Hello World'

    @expose('hello.xhtml')
    def hello(self, person=None):
        return dict(person=person)


if __name__ == "__main__":

    config = AppConfig(minimal=True, root_controller=RootController())

    config.renderers = ['kajiki']

    config['helpers'] = webhelpers2

    config.serve_static = True
    config.paths['static_files'] = 'public'

    application = config.make_wsgi_app()

    print("Serving on port 8080...")
    httpd = make_server('', 8080, application)
    httpd.serve_forever()