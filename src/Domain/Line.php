<?php

namespace App\Domain;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'lines')]
final class Line
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[Column(type: 'string', length: 255)]
  private string $type;

  #[Column(type: 'datetimetz_immutable', nullable: false)]
  private \DateTimeImmutable $date; 

  #[Column(type: 'string', length: 255)]
  private string $name;

  #[Column(type: 'string', length: 255, nullable: true)]
  private string $label;

  #[Column(type: 'float')]
  private float $amount;

  #[Column(type: 'simple_array')]
  private array $breakdown;

  #[Column(type: 'text', nullable: true)]
  private string $description;

  public function __construct() {
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function setType(string $type): void
  {
    $this->type = $type;
  }

  public function getDate(): \DateTimeImmutable
  {
    return $this->date;
  }

  public function setDate(\DateTimeImmutable $date): void
  {
    $this->date = $date;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function getLabel(): string
  {
    return $this->label;
  }

  public function setLabel(string $label): void
  {
    $this->label = $label;
  }

  public function getAmount(): float
  {
    return $this->amount;
  }

  public function setAmount(float $amount): void
  {
    $this->amount = $amount;
  }
  
  public function getBreakdown(): array
  {
    return $this->breakdown;
  }

  public function setBreakdown(array $breakdown): void
  {
    $this->breakdown = $breakdown;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function setDescription(string $description): void
  {
    $this->description = $description;
  }
}