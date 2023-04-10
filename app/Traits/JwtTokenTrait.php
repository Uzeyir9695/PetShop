<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;

trait JwtTokenTrait
{
    /**
     * Generate a JWT token.
     *
     * @param int $id
     * @return string
     */
    protected function generateJwtToken($data)
    {
        $encoder = new JoseEncoder(); // use a different encoder
        $claimFormatter = new ChainedFormatter(); // use a different claim formatter
        $signer = new Sha256();
        $privateKey = file_get_contents('../keys/private.key');
        $key = InMemory::plainText($privateKey);
        $builder = (new Builder($encoder, $claimFormatter))
            ->issuedBy(config('app.url'))
            ->permittedFor(config('app.url'))
            ->identifiedBy(Str::uuid())
            ->issuedAt(new \DateTimeImmutable())
            ->withClaim('user_data', $data);

        if (isset($data['expires_at'])) {
            $builder = $builder->expiresAt($data['expires_at']);
        }

        $token = $builder->getToken($signer, $key);

        return  $token;
    }
}
