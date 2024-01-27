<?php

namespace Supaapps\Guard\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 *
 */
class JwtAuthDriver implements Guard
{
    use GuardHelpers;

    /**
     * @var Request|array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed|string|null
     */
    private Request $request;

    /**
     * @var stdClass
     */
    private stdClass $jwtPayload;

    /**
     * @var string
     */
    private string $firstName;
    /**
     * @var string
     */
    private string $lastName;
    /**
     * @var string
     */
    private string $email;

    /**
     * @var array
     */
    private array $scopesArray;
    /**
     * @var string
     */
    private string $scopes;
    /**
     * @var bool
     */
    private bool $admin;

    /**
     * @var string
     */
    private string $publicKey;
    /**
     * @var string
     */
    private string $realmName;


    /**
     * @param EloquentUserProvider|null $provider
     */
    public function __construct(
        ?EloquentUserProvider $provider,
    ) {
        $this->provider = $provider;
        $this->request = request();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        $this->validate();

        if ($user = $this->provider->retrieveById($this->jwtPayload->sub)) {
            return $user;
        }

        return $this->provider
            ->createModel()
            ->create([
                'id' => $this->jwtPayload->sub,
            ]);
    }


    /**
     * @return false|string
     */
    public function fetchPublicKey()
    {
        $url = trim(config('sguard.auth_server_url'),'/') . '/keys/public_key';
        return file_get_contents($url);
    }

    /**
     * @return false|string
     */
    public function fetchAlgo()
    {
        $url = trim(config('sguard.auth_server_url'),'/') . '/keys/algo';
        return file_get_contents($url);
    }

    /**
     * Validate a token signature
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (is_null($bearerToken = $this->request->bearerToken())) {
            throw new AuthenticationException("A valid bearer token is required");
        }

        try {
            $publicKey = Cache::remember('supaapps_jwt/public_key', 600, function () {
                return $this->fetchPublicKey();
            });
            $algorithm = Cache::remember('supaapps_jwt/algo', 600, function () {
                return $this->fetchAlgo();
            });
            $this->jwtPayload = JWT::decode($bearerToken, new Key(trim($publicKey), trim($algorithm)));
            if ( config('sguard.realm_name') !== $this->jwtPayload->aud) {
                throw new AuthenticationException('Auth error - realm mismatch');
            }
            $this->firstName = $this->jwtPayload->first_name;
            $this->lastName = $this->jwtPayload->last_name;
            $this->email = $this->jwtPayload->email;
            $this->scopesArray = explode(' ', $this->jwtPayload->scopes);
            $this->scopes = $this->jwtPayload->scopes;
            if (strpos($this->scopes, '/' . config('sguard.realm_name') .'/*') !== false) {
                $this->admin = true;
            } else {
                $this->admin = false;
            }

        } catch (\Throwable $ex) {
            throw new AuthenticationException('Auth error - ' . $ex->getMessage());
        }


        return true;
    }


    /**
     * @return string
     */
    public function firstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function lastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function email(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function scopes(): string
    {
        return $this->scopes;
    }

    /**
     * @return array
     */
    public function scopesArray(): array
    {
        return $this->scopesArray;
    }
}
