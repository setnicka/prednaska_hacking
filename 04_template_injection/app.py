#!/usr/bin/python3

from flask import Flask, Blueprint, redirect, render_template
bp = Blueprint("app", __name__)


def create_app():
    app = Flask(__name__)
    app.register_blueprint(bp, url_prefix="/")
    return app


@bp.route('/hello/<path:userstring>')
def hello(userstring):
    message = eval('"Hello ' + userstring + '"')
    return render_template('index.html', message=message)


@bp.route('/')
def redirect_to_user():
    return redirect("/hello/user", code=302)
