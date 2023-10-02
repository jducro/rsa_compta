<?php

namespace App\Domain;

final class LineBreakdown {
  public const PlaneRenewal = "PlaneRenewal"; // Renouvellement
  public const CustomerFees = "CustomerFees"; // Frais client
  public const RSAContribution = "RSAContribution"; // Cotisation RSA
  public const RSANavContribution = "RSANavContribution"; // Cotisation RSA Nav
  public const FollowUpNav = "FollowUpNav"; // Suivi Nav
  public const InternalTransfer = "InternalTransfer"; // Virement interne
  public const PenRefund = "PenRefund"; // Remboursement PEN
  public const Meeting = "Meeting"; // Réunion / Séminaire
  public const PaypalFees = "PaypalFees"; // Frais Paypal
  public const SogecomFees = "SogecomFees"; // Frais Sogecom
  public const Osac = "Osac"; // OSAC
  public const Other = "Other"; // Autre
  public const Donation = "Donation"; // Don
  public const DonationAdvance = "DonationAdvance"; // Avance don
  public const VibrationDebit = "VibrationDebit"; // Vibration Debit
  public const VibrationCredit = "VibrationCredit"; // Vibration Credit
}