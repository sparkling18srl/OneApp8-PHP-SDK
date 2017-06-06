<?php

namespace OneApp8\Model;

/**
 * Class Order model
 * @author globrutto
 */
class Order
{
    private $id;
    private $walletId;

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setWalletId(string $walletId)
    {
        $this->walletId = $walletId;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }
}
