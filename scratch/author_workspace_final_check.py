import urllib.request
import json

base_url = 'http://localhost:8000/api'
sub_id = 14

def login():
    req = urllib.request.Request(f"{base_url}/login", data=json.dumps({'email': 'tes@yaya.com', 'password': 'tes1234567'}).encode('utf-8'))
    req.add_header('Content-Type', 'application/json')
    req.add_header('Accept', 'application/json')
    with urllib.request.urlopen(req) as response:
        body = json.loads(response.read().decode('utf-8'))
        return body.get('token') or body.get('access_token') or body.get('data', {}).get('token')

token = login()
print(f"Logged in as tes@yaya.com")

def get(url, token):
    req = urllib.request.Request(url)
    req.add_header('Accept', 'application/json')
    if token:
        req.add_header('Authorization', f'Bearer {token}')
    try:
        with urllib.request.urlopen(req) as response:
            return response.getcode(), json.loads(response.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        return e.code, json.loads(e.read().decode('utf-8'))

code, body = get(f"{base_url}/submissions", token)
print(f"GET /api/submissions: {code} (Found {len(body.get('data', []))} submissions)")

code, body = get(f"{base_url}/submissions/{sub_id}", token)
print(f"GET /api/submissions/{sub_id}: {code}")

code, body = get(f"{base_url}/journals", token)
print(f"GET /api/journals: {code} (Found {len(body.get('data', []))} journals)")

code, body = get(f"{base_url}/user/journals/test-journal/management/submissions", token)
print(f"GET /api/user/journals/test-journal/management/submissions (Security Regression Check): {code}")
