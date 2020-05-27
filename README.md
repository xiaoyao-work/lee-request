Guzzle Http库已经提供了强大的PHP的HTTP请求；所以此项目废弃


发送请求
你可以使用Guzzle的 GuzzleHttp\ClientInterface 对象来发送请求。

创建客户端
use GuzzleHttp\Client;

$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'http://httpbin.org',
    // You can set any number of default request options.
    'timeout'  => 2.0,
]);
Client对象可以接收一个包含参数的数组：

base_uri
(string|UriInterface) 基URI用来合并到相关URI，可以是一个字符串或者UriInterface的实例，当提供了相关uri，将合并到基URI，遵循的规则请参考 RFC 3986, section 2 章节。

// Create a client with a base URI
$client = new GuzzleHttp\Client(['base_uri' => 'https://foo.com/api/']);
// Send a request to https://foo.com/api/test
$response = $client->request('GET', 'test');
// Send a request to https://foo.com/root
$response = $client->request('GET', '/root');
不想阅读RFC 3986？这里有一些关于 base_uri 与其他URI处理器的快速例子：

base_uri	URI	Result
http://foo.com	/bar	http://foo.com/bar
http://foo.com/foo	/bar	http://foo.com/bar
http://foo.com/foo	bar	http://foo.com/bar
http://foo.com/foo/	bar	http://foo.com/foo/bar
http://foo.com	http://baz.com	http://baz.com
http://foo.com/?bar	bar	http://foo.com/bar
handler
传输HTTP请求的(回调)函数。 该函数被调用的时候包含 Psr7\Http\Message\RequestInterface 以及参数数组，必须返回 GuzzleHttp\Promise\PromiseInterface ，成功时满足 Psr7\Http\Message\ResponseInterface 。 handler 是一个构造方法，不能在请求参数里被重写。
...
(混合) 构造方法中传入的其他所有参数用来当作每次请求的默认参数。
发送请求
Client对象的方法可以很容易的发送请求：

$response = $client->get('http://httpbin.org/get');
$response = $client->delete('http://httpbin.org/delete');
$response = $client->head('http://httpbin.org/get');
$response = $client->options('http://httpbin.org/get');
$response = $client->patch('http://httpbin.org/patch');
$response = $client->post('http://httpbin.org/post');
$response = $client->put('http://httpbin.org/put');
你可以创建一个请求，一切就绪后将请求传送给client：

use GuzzleHttp\Psr7\Request;

$request = new Request('PUT', 'http://httpbin.org/put');
$response = $client->send($request, ['timeout' => 2]);
Client对象为传输请求提供了非常灵活的处理器方式，包括请求参数、每次请求使用的中间件以及传送多个相关请求的基URI。

你可以在 Handlers and Middleware 页面找到更多关于中间件的内容。

异步请求
你可以使用Client提供的方法来创建异步请求：

$promise = $client->getAsync('http://httpbin.org/get');
$promise = $client->deleteAsync('http://httpbin.org/delete');
$promise = $client->headAsync('http://httpbin.org/get');
$promise = $client->optionsAsync('http://httpbin.org/get');
$promise = $client->patchAsync('http://httpbin.org/patch');
$promise = $client->postAsync('http://httpbin.org/post');
$promise = $client->putAsync('http://httpbin.org/put');
你也可以使用Client的 sendAsync() and requestAsync() 方法：

use GuzzleHttp\Psr7\Request;

// Create a PSR-7 request object to send
$headers = ['X-Foo' => 'Bar'];
$body = 'Hello!';
$request = new Request('HEAD', 'http://httpbin.org/head', $headers, $body);

// Or, if you don't need to pass in a request instance:
$promise = $client->requestAsync('GET', 'http://httpbin.org/get');
这些方法返回了Promise对象，该对象实现了由 Guzzle promises library 提供的 Promises/A+ spec ，这意味着你可以使用 then() 来调用返回值，成功使用 Psr\Http\Message\ResponseInterface 处理器，否则抛出一个异常。

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

$promise = $client->requestAsync('GET', 'http://httpbin.org/get');
$promise->then(
    function (ResponseInterface $res) {
        echo $res->getStatusCode() . "\n";
    },
    function (RequestException $e) {
        echo $e->getMessage() . "\n";
        echo $e->getRequest()->getMethod();
    }
);
并发请求
你可以使用Promise和异步请求来同时发送多个请求：

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

$client = new Client(['base_uri' => 'http://httpbin.org/']);

// Initiate each request but do not block
$promises = [
    'image' => $client->getAsync('/image'),
    'png'   => $client->getAsync('/image/png'),
    'jpeg'  => $client->getAsync('/image/jpeg'),
    'webp'  => $client->getAsync('/image/webp')
];

// Wait on all of the requests to complete.
$results = Promise\unwrap($promises);

// You can access each result using the key provided to the unwrap
// function.
echo $results['image']->getHeader('Content-Length');
echo $results['png']->getHeader('Content-Length');
当你想发送不确定数量的请求时，可以使用 GuzzleHttp\Pool 对象：

use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

$client = new Client();

$requests = function ($total) {
    $uri = 'http://127.0.0.1:8126/guzzle-server/perf';
    for ($i = 0; $i < $total; $i++) {
        yield new Request('GET', $uri);
    }
};

$pool = new Pool($client, $requests(100), [
    'concurrency' => 5,
    'fulfilled' => function ($response, $index) {
        // this is delivered each successful response
    },
    'rejected' => function ($reason, $index) {
        // this is delivered each failed request
    },
]);

// Initiate the transfers and create a promise
$promise = $pool->promise();

// Force the pool of requests to complete.
$promise->wait();
使用响应
前面的例子里，我们取到了 $response 变量，或者从Promise得到了响应，Response对象实现了一个PSR-7接口 Psr\Http\Message\ResponseInterface ， 包含了很多有用的信息。

你可以获取这个响应的状态码和和原因短语(reason phrase)：

$code = $response->getStatusCode(); // 200
$reason = $response->getReasonPhrase(); // OK
你可以从响应获取头信息(header)：

// Check if a header exists.
if ($response->hasHeader('Content-Length')) {
    echo "It exists";
}

// Get a header from the response.
echo $response->getHeader('Content-Length');

// Get all of the response headers.
foreach ($response->getHeaders() as $name => $values) {
    echo $name . ': ' . implode(', ', $values) . "\r\n";
}
使用 getBody 方法可以获取响应的主体部分(body)，主体可以当成一个字符串或流对象使用

$body = $response->getBody();
// Implicitly cast the body to a string and echo it
echo $body;
// Explicitly cast the body to a string
$stringBody = (string) $body;
// Read 10 bytes from the body
$tenBytes = $body->read(10);
// Read the remaining contents of the body as a string
$remainingBytes = $body->getContents();
查询字符串参数
你可以有多种方式来提供请求的查询字符串 你可以在请求的URI中设置查询字符串：

$response = $client->request('GET', 'http://httpbin.org?foo=bar');
你可以使用 query 请求参数来声明查询字符串参数：

$client->request('GET', 'http://httpbin.org', [
    'query' => ['foo' => 'bar']
]);
提供的数组参数将会使用PHP的 http_build_query ：

最后，你可以提供一个字符串作为 query 请求参数：

$client->request('GET', 'http://httpbin.org', ['query' => 'foo=bar']);
上传数据
Guzzle为上传数据提供了一些方法。 你可以发送一个包含数据流的请求，将 body 请求参数设置成一个字符串、 fopen 返回的资源、或者一个 Psr\Http\Message\StreamInterface 的实例。

// Provide the body as a string.
$r = $client->request('POST', 'http://httpbin.org/post', [
    'body' => 'raw data'
]);

// Provide an fopen resource.
$body = fopen('/path/to/file', 'r');
$r = $client->request('POST', 'http://httpbin.org/post', ['body' => $body]);

// Use the stream_for() function to create a PSR-7 stream.
$body = \GuzzleHttp\Psr7\stream_for('hello!');
$r = $client->request('POST', 'http://httpbin.org/post', ['body' => $body]);
上传JSON数据以及设置合适的头信息可以使用 json 请求参数这个简单的方式：

$r = $client->request('PUT', 'http://httpbin.org/put', [
    'json' => ['foo' => 'bar']
]);
POST/表单请求
除了使用 body 参数来指定请求数据外，Guzzle为发送POST数据提供了有用的方法。

发送表单字段
发送 application/x-www-form-urlencoded POST请求需要你传入 form_params 数组参数，数组内指定POST的字段。

$response = $client->request('POST', 'http://httpbin.org/post', [
    'form_params' => [
        'field_name' => 'abc',
        'other_field' => '123',
        'nested_field' => [
            'nested' => 'hello'
        ]
    ]
]);
发送表单文件
你可以通过使用 multipart 请求参数来发送表单(表单enctype属性需要设置 multipart/form-data )文件， 该参数接收一个包含多个关联数组的数组，每个关联数组包含一下键名：

name: (必须，字符串) 映射到表单字段的名称。
contents: (必须，混合) 提供一个字符串，可以是 fopen 返回的资源、或者一个
Psr\Http\Message\StreamInterface 的实例。

$response = $client->request('POST', 'http://httpbin.org/post', [
    'multipart' => [
        [
            'name'     => 'field_name',
            'contents' => 'abc'
        ],
        [
            'name'     => 'file_name',
            'contents' => fopen('/path/to/file', 'r')
        ],
        [
            'name'     => 'other_file',
            'contents' => 'hello',
            'filename' => 'filename.txt',
            'headers'  => [
                'X-Foo' => 'this is an extra header to include'
            ]
        ]
    ]
]);
Cookies
Guzzle可以使用 cookies 请求参数为你维护一个cookie会话，当发送一个请求时， cookies 选项必须设置成 GuzzleHttp\Cookie\CookieJarInterface 的实例。

// Use a specific cookie jar
$jar = new \GuzzleHttp\Cookie\CookieJar;
$r = $client->request('GET', 'http://httpbin.org/cookies', [
    'cookies' => $jar
]);
You can set cookies to true in a client constructor if you would like to use a shared cookie jar for all requests.

// Use a shared client cookie jar
$client = new \GuzzleHttp\Client(['cookies' => true]);
$r = $client->request('GET', 'http://httpbin.org/cookies');
重定向
如果你没有告诉Guzzle不要重定向，Guzzle会自动的进行重定向，你可以使用 allow_redirects 请求参数来自定义重定向行为。

设置成 true 时将启用最大数量为5的重定向，这是默认设置。
设置成 false 来禁用重定向。
传入一个包含 max 键名的关联数组来声明最大重定向次数，提供可选的 strict 键名来声明是否使用严格的RFC标准重定向 (表示使用POST请求重定向POST请求 vs 大部分浏览器使用GET请求重定向POST请求)。
$response = $client->request('GET', 'http://github.com');
echo $response->getStatusCode();
// 200
下面的列子表示重定向被禁止：

$response = $client->request('GET', 'http://github.com', [
    'allow_redirects' => false
]);
echo $response->getStatusCode();
// 301
异常
请求传输过程中出现的错误Guzzle将会抛出异常。

在发送网络错误(连接超时、DNS错误等)时，将会抛出 GuzzleHttp\Exception\RequestException 异常。 该异常继承自 GuzzleHttp\Exception\TransferException ，捕获这个异常可以在传输请求过程中抛出异常。

use GuzzleHttp\Exception\RequestException;

try {
    $client->request('GET', 'https://github.com/_abc_123_404');
} catch (RequestException $e) {
    echo $e->getRequest();
    if ($e->hasResponse()) {
        echo $e->getResponse();
    }
}
GuzzleHttp\Exception\ConnectException 异常发生在网络错误时， 该异常继承自 GuzzleHttp\Exception\RequestException 。

如果 http_errors 请求参数设置成true，在400级别的错误的时候将会抛出 GuzzleHttp\Exception\ClientException 异常， 该异常继承自 GuzzleHttp\Exception\BadResponseException GuzzleHttp\Exception\BadResponseException 继承自 GuzzleHttp\Exception\RequestException 。

use GuzzleHttp\Exception\ClientException;

try {
    $client->request('GET', 'https://github.com/_abc_123_404');
} catch (ClientException $e) {
    echo $e->getRequest();
    echo $e->getResponse();
}
如果 http_errors 请求参数设置成true，在500级别的错误的时候将会抛出 GuzzleHttp\Exception\ServerException 异常。 该异常继承自 GuzzleHttp\Exception\BadResponseException 。

GuzzleHttp\Exception\TooManyRedirectsException 异常发生在重定向次数过多时， 该异常继承自 GuzzleHttp\Exception\RequestException 。

上述所有异常均继承自 GuzzleHttp\Exception\TransferException 。

环境变量
Guzzle提供了一些可自定义的环境变量：

GUZZLE_CURL_SELECT_TIMEOUT
当在curl处理器时使用 curl_multi_select() 控制了 curl_multi_* 需要使用到的持续时间， 有些系统实现PHP的 curl_multi_select() 存在问题，调用该函数时总是等待超时的最大值。
HTTP_PROXY
定义了使用http协议发送请求时使用的代理。
HTTPS_PROXY
定义了使用https协议发送请求时使用的代理。
相关ini设置
Guzzle配置客户端时可以利用PHP的ini配置。

openssl.cafile
当发送到"https"协议的请求时需要用到指定磁盘上PEM格式的CA文件，参考： https://wiki.php.net/rfc/tls-peer-verification#phpini_defaults
