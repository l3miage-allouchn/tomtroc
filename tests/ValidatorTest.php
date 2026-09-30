<?php

use PHPUnit\Framework\TestCase;

// tests des règles de validation des formulaires (src/Core/Validator.php)
// chaque test vérifie un cas : une valeur correcte doit donner null, une valeur incorrecte un message d'erreur
class ValidatorTest extends TestCase
{
    // ---------- utilisateur (inscription, Mon compte) ----------

    public function testValidUserIsAccepted(): void
    {
        $this->assertNull(Validator::validateUser('Nathalire', 'nathalie@mail.com', 'password123', true));
    }

    public function testEmptyPseudoIsRejected(): void
    {
        $error = Validator::validateUser('', 'nathalie@mail.com', 'password123', true);

        $this->assertSame('Le pseudo est obligatoire.', $error);
    }

    public function testPseudoLongerThan50CharactersIsRejected(): void
    {
        $this->assertNotNull(Validator::validateUser(str_repeat('a', 51), 'nathalie@mail.com', 'password123', true));
    }

    public function testPseudoLengthCountsCharactersNotBytes(): void
    {
        // 50 caractères accentués (100 octets en UTF-8) : doit être accepté
        $this->assertNull(Validator::validateUser(str_repeat('é', 50), 'nathalie@mail.com', 'password123', true));
    }

    public function testInvalidEmailIsRejected(): void
    {
        $error = Validator::validateUser('Nathalire', 'pas-un-email', 'password123', true);

        $this->assertSame("L'adresse email n'est pas valide.", $error);
    }

    public function testPasswordShorterThan8CharactersIsRejected(): void
    {
        $this->assertNotNull(Validator::validateUser('Nathalire', 'nathalie@mail.com', '1234567', true));
    }

    public function testPasswordOf8CharactersIsAccepted(): void
    {
        $this->assertNull(Validator::validateUser('Nathalire', 'nathalie@mail.com', '12345678', true));
    }

    public function testEmptyPasswordIsRejectedWhenRequired(): void
    {
        $this->assertNotNull(Validator::validateUser('Nathalire', 'nathalie@mail.com', '', true));
    }

    public function testEmptyPasswordIsAcceptedWhenOptional(): void
    {
        // Mon compte : mot de passe vide = on garde l'ancien
        $this->assertNull(Validator::validateUser('Nathalire', 'nathalie@mail.com', '', false));
    }

    public function testShortPasswordIsRejectedEvenWhenOptional(): void
    {
        $this->assertNotNull(Validator::validateUser('Nathalire', 'nathalie@mail.com', '123', false));
    }

    // ---------- livre ----------

    public function testValidBookIsAccepted(): void
    {
        $this->assertNull(Validator::validateBook('Dune', 'Frank Herbert', 'available'));
        $this->assertNull(Validator::validateBook('Dune', 'Frank Herbert', 'unavailable'));
    }

    public function testEmptyTitleIsRejected(): void
    {
        $this->assertNotNull(Validator::validateBook('', 'Frank Herbert', 'available'));
    }

    public function testTitleLongerThan255CharactersIsRejected(): void
    {
        $this->assertNotNull(Validator::validateBook(str_repeat('a', 256), 'Frank Herbert', 'available'));
    }

    public function testEmptyAuthorIsRejected(): void
    {
        $this->assertNotNull(Validator::validateBook('Dune', '', 'available'));
    }

    public function testUnknownStatusIsRejected(): void
    {
        $error = Validator::validateBook('Dune', 'Frank Herbert', 'pirate');

        $this->assertSame('Le statut de disponibilité est invalide.', $error);
    }

    public function testStatusComparisonIsStrict(): void
    {
        // la liste blanche compare aussi le type et la casse
        $this->assertNotNull(Validator::validateBook('Dune', 'Frank Herbert', 'AVAILABLE'));
        $this->assertNotNull(Validator::validateBook('Dune', 'Frank Herbert', ''));
    }

    // ---------- message ----------

    public function testValidMessageIsAccepted(): void
    {
        $this->assertNull(Validator::validateMessage('Bonjour, ton livre est-il disponible ?'));
    }

    public function testEmptyMessageIsRejected(): void
    {
        $this->assertNotNull(Validator::validateMessage(''));
    }

    public function testMessageOf1000CharactersIsAccepted(): void
    {
        $this->assertNull(Validator::validateMessage(str_repeat('x', 1000)));
    }

    public function testMessageLongerThan1000CharactersIsRejected(): void
    {
        $this->assertNotNull(Validator::validateMessage(str_repeat('x', 1001)));
    }
}
