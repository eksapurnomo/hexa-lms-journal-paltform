import urllib.request
import json

base_url = 'http://localhost:8000/api'
journal_slug = 'test-journal'
sub_id = 14

def login():
    req = urllib.request.Request(f"{base_url}/login", data=json.dumps({'email': 'admin@hexalms.local', 'password': 'password'}).encode('utf-8'))
    req.add_header('Content-Type', 'application/json')
    req.add_header('Accept', 'application/json')
    with urllib.request.urlopen(req) as response:
        body = json.loads(response.read().decode('utf-8'))
        return body.get('token') or body.get('access_token') or body.get('data', {}).get('token')

token = login()

print("10. Checking Editorial Visibility as Admin...")
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

code, body = get(f"{base_url}/user/journals/{journal_slug}/management/submissions", token)
if code == 200:
    found = any(s['id'] == sub_id for s in body.get('data', []))
    print(f"Editorial access Code: {code}")
    print(f"Submission in Management list: {'YES' if found else 'NO'}")
else:
    print(f"Editorial access Code: {code}")

