<?php

namespace App\Repository;

use App\Entity\Usuario;
use App\Entity\UsuarioFcmToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UsuarioFcmTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioFcmToken::class);
    }

    public function registrarToken(Usuario $usuario, string $token, ?string $plataforma): UsuarioFcmToken
    {
        $registro = $this->findOneBy(['token' => $token]);

        if (!$registro) {
            $registro = new UsuarioFcmToken();
            $registro->setToken($token);
            $registro->setFechaRegistro(new \DateTime());
        }

        $registro->setUsuario($usuario);
        $registro->setPlataforma($plataforma);
        $registro->setActivo(true);
        $registro->setFechaActualizacion(new \DateTime());

        $em = $this->getEntityManager();
        $em->persist($registro);
        $em->flush();

        return $registro;
    }

    /**
     * @return string[]
     */
    public function findTokensActivos(): array
    {
        $registros = $this->findBy(['activo' => true]);

        return array_map(function (UsuarioFcmToken $r) {
            return $r->getToken();
        }, $registros);
    }

    public function desactivarToken(string $token): void
    {
        $registro = $this->findOneBy(['token' => $token]);

        if ($registro) {
            $registro->setActivo(false);
            $this->getEntityManager()->flush();
        }
    }
}
