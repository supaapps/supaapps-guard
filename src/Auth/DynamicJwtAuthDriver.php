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
use Throwable;

class DynamicJwtAuthDriver implements Guard
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
        if (!$this->validate()) {
            return null;
        }

        if (!is_null($this->user)) {
            return $this->user;
        }

        // retrieve user from db or create a new user by id
        if (is_null($user = $this->provider->retrieveById($this->jwtPayload->sub))) {
            $model = $this->provider->createModel();
            $model->forceCreate([
                'id' => $this->jwtPayload->sub
            ]);
            $user = $model->query()->where('id', $this->jwtPayload->sub)->first();
        }

        $this->setUser($user);
        $this->afterRetrieveUserValidation();
        return $user;
    }


    /**
     * @return false|string
     */
    public function fetchPublicKey()
    {
        $url = rtrim(config('sguard.auth_server_url'), '/') . '/public/public_key';
        return file_get_contents($url);
    }

    /**
     * @return false|string
     */
    public function fetchAlgo()
    {
        $url = rtrim(config('sguard.auth_server_url'), '/') . '/public/algo';
        return file_get_contents($url);
    }

    /**
     * @return array
     */
    public function fetchRevokedTokens(): array
    {
        try {
            $url = rtrim(config('sguard.auth_server_url'), '/') . '/public/revoked_ids';
            return json_decode(file_get_contents($url));
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Validate a token signature
     *
     * @param array $credentials
     * @return bool
     * @throws AuthenticationException
     */
    public function validate(array $credentials = []): bool
    {
        if (is_null($bearerToken = $this->request->bearerToken())) {
            return false;
        }

        try {
            $publicKey = Cache::remember('supaapps_jwt/public_key', 600, function () {
                return $this->fetchPublicKey();
            });

            $algorithm = Cache::remember('supaapps_jwt/algo', 600, function () {
                return $this->fetchAlgo();
            });
            $this->jwtPayload = JWT::decode($bearerToken, new Key(trim($publicKey), trim($algorithm)));
            $revokedIds = Cache::remember('supaapps_jwt/revoked_ids', 15, function () {
                return $this->fetchRevokedTokens();
            });
            if (in_array($this->jwtPayload->id, $revokedIds)) {
                throw new AuthenticationException('access token has been revoked');
            }
            $this->firstName = $this->jwtPayload->first_name;
            $this->lastName = $this->jwtPayload->last_name;
            $this->email = $this->jwtPayload->email;
            $this->scopesArray = explode(' ', $this->jwtPayload->scopes);
            $this->scopes = $this->jwtPayload->scopes;
        } catch (Throwable $ex) {
            throw new AuthenticationException('Auth error - ' . $ex->getMessage());
        }

        return true;
    }

    public function afterRetrieveUserValidation()
    {
        if ($this->user->realm->name !== $this->jwtPayload->aud) {
            throw new AuthenticationException('Auth error - realm mismatch');
        }

        if (strpos($this->scopes, '/' . $this->jwtPayload->aud . '/*') !== false) {
            $this->admin = true;
        } else {
            $this->admin = false;
        }
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
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
