#!/usr/bin/python3

from http.server import BaseHTTPRequestHandler, HTTPServer
from urllib.parse import urlparse, parse_qs
from datetime import datetime


class MyServer(BaseHTTPRequestHandler):
    def do_GET(self):
        query_components = parse_qs(urlparse(self.path).query)
        print(f"{datetime.now()} {self.client_address[0]} {self.path}")
        for k, v in query_components.items():
            print(f"\t{k}\t{v}")

        self.send_response(404)
        self.end_headers()


if __name__ == "__main__":
    try:
        server = HTTPServer(('0.0.0.0', 8888), MyServer)
        print('Started http server on :8888')
        server.serve_forever()
    except KeyboardInterrupt:
        print('^C received, shutting down server')
        server.socket.close()
