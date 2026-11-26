# JWT 7 Compatibility

The next major release requires firebase/php-jwt7.1.1 or newer. The guard's public
API is unchanged, but the dependency deliberately rejects undersized keys and
invalid numeric JWT time claims. Do not relax these checks to retain weak keys.
Inspect the signing system's public key before upgrading consumers; never rotate
production signing keys implicitly as part of a library deployment.

Existing v1 consumers keep their released dependency constraints until they
explicitly adopt the next major. No published tag is overwritten. Laravel12 is
the current test target; applications must also run their authentication and
authorization regression suites before deployment.
