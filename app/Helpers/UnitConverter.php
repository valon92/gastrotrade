<?php

namespace App\Helpers;

class UnitConverter
{
    /**
     * Convert kg to pieces for Kese Mbeturinash based on size
     * 
     * @param float $kg Quantity in kg
     * @param int $size Size in liters (e.g., 40, 70, 300)
     * @return float Quantity in pieces
     */
    public static function kgToPieces($kg, $size)
    {
        $piecesPerKg = self::getPiecesPerKgForKeseMbeturinash($size);
        return $kg * $piecesPerKg;
    }
    
    /**
     * Convert pieces to packages (kompleti)
     * 
     * @param float $pieces Quantity in pieces
     * @param int $piecesPerPackage Number of pieces per package
     * @return float Quantity in packages
     */
    public static function piecesToPackages($pieces, $piecesPerPackage)
    {
        if ($piecesPerPackage <= 0) {
            return $pieces;
        }
        return $pieces / $piecesPerPackage;
    }
    
    /**
     * Convert packages to pieces
     * 
     * @param float $packages Quantity in packages
     * @param int $piecesPerPackage Number of pieces per package
     * @return float Quantity in pieces
     */
    public static function packagesToPieces($packages, $piecesPerPackage)
    {
        return $packages * $piecesPerPackage;
    }
    
    /**
     * Convert kg to packages for Kese Mbeturinash
     * 
     * @param float $kg Quantity in kg
     * @param int $size Size in liters
     * @param int $piecesPerPackage Number of pieces per package (default 20)
     * @return float Quantity in packages
     */
    public static function kgToPackages($kg, $size, $piecesPerPackage = 20)
    {
        $pieces = self::kgToPieces($kg, $size);
        return self::piecesToPackages($pieces, $piecesPerPackage);
    }
    
    /**
     * Convert packages to kg for Kese Mbeturinash
     * 
     * @param float $packages Quantity in packages
     * @param int $size Size in liters
     * @param int $piecesPerPackage Number of pieces per package (default 20)
     * @return float Quantity in kg
     */
    public static function packagesToKg($packages, $size, $piecesPerPackage = 20)
    {
        $pieces = self::packagesToPieces($packages, $piecesPerPackage);
        $piecesPerKg = self::getPiecesPerKgForKeseMbeturinash($size);
        return $pieces / $piecesPerKg;
    }
    
    /**
     * Get pieces per kg for Kese Mbeturinash based on size
     * 
     * @param int $size Size in liters (e.g., 40, 70, 300)
     * @return float Pieces per kg
     */
    public static function getPiecesPerKgForKeseMbeturinash($size)
    {
        $conversionMap = [
            40 => 20,   // 1 kg = 20 pieces (1 kompleti = 20cp ≈ 1kg)
            70 => 15,   // 1 kg ≈ 15 pieces
            120 => 10,  // 1 kg ≈ 10 pieces
            150 => 8,   // 1 kg ≈ 8 pieces
            170 => 7,   // 1 kg ≈ 7 pieces
            200 => 5,   // 1 kg ≈ 5 pieces
            240 => 4,   // 1 kg ≈ 4 pieces
            270 => 3.5, // 1 kg ≈ 3.5 pieces
            300 => 3,   // 1 kg ≈ 3 pieces
        ];
        
        return $conversionMap[$size] ?? 20; // Default to 20 if size not found
    }
    
    /**
     * Extract size (e.g., 40L, 70L, 300L) from Kese Mbeturinash product name
     * 
     * @param string $name Product name
     * @return int Size in liters, or 0 if not found
     */
    public static function extractSizeFromKeseMbeturinash($name)
    {
        if (preg_match('/(\d+)L/i', $name, $matches)) {
            return (int)$matches[1];
        }
        return 0;
    }
    
    /**
     * Convert quantity from one unit to another for a product
     * 
     * @param float $quantity Quantity to convert
     * @param string $fromUnit Source unit ('kg', 'cp', 'package')
     * @param string $toUnit Target unit ('kg', 'cp', 'package')
     * @param \App\Models\Product $product Product model
     * @return float Converted quantity
     */
    public static function convert($quantity, $fromUnit, $toUnit, $product)
    {
        if ($fromUnit === $toUnit) {
            return $quantity;
        }
        
        $isKeseMbeturinash = stripos($product->name, 'kese mbeturinash') !== false;
        
        // First convert to pieces (cp) as base unit
        $pieces = self::toPieces($quantity, $fromUnit, $product, $isKeseMbeturinash);
        
        // Then convert from pieces to target unit
        return self::fromPieces($pieces, $toUnit, $product, $isKeseMbeturinash);
    }
    
    /**
     * Convert any unit to pieces (cp)
     * 
     * @param float $quantity Quantity to convert
     * @param string $unit Source unit ('kg', 'cp', 'package')
     * @param \App\Models\Product $product Product model
     * @param bool $isKeseMbeturinash Whether product is Kese Mbeturinash
     * @return float Quantity in pieces
     */
    private static function toPieces($quantity, $unit, $product, $isKeseMbeturinash)
    {
        switch ($unit) {
            case 'kg':
                if ($isKeseMbeturinash) {
                    $size = self::extractSizeFromKeseMbeturinash($product->name);
                    return self::kgToPieces($quantity, $size);
                }
                return $quantity; // For non-Kese Mbeturinash, kg stays as is (not applicable)
                
            case 'package':
                if ($product->sold_by_package && $product->pieces_per_package) {
                    return self::packagesToPieces($quantity, $product->pieces_per_package);
                }
                return $quantity; // If not sold by package, treat as pieces
                
            case 'cp':
            default:
                return $quantity; // Already in pieces
        }
    }
    
    /**
     * Convert pieces (cp) to any unit
     * 
     * @param float $pieces Quantity in pieces
     * @param string $unit Target unit ('kg', 'cp', 'package')
     * @param \App\Models\Product $product Product model
     * @param bool $isKeseMbeturinash Whether product is Kese Mbeturinash
     * @return float Converted quantity
     */
    private static function fromPieces($pieces, $unit, $product, $isKeseMbeturinash)
    {
        switch ($unit) {
            case 'kg':
                if ($isKeseMbeturinash) {
                    $size = self::extractSizeFromKeseMbeturinash($product->name);
                    $piecesPerKg = self::getPiecesPerKgForKeseMbeturinash($size);
                    return $pieces / $piecesPerKg;
                }
                return $pieces; // For non-Kese Mbeturinash, return as is (not applicable)
                
            case 'package':
                if ($product->sold_by_package && $product->pieces_per_package) {
                    return self::piecesToPackages($pieces, $product->pieces_per_package);
                }
                return $pieces; // If not sold by package, return as pieces
                
            case 'cp':
            default:
                return $pieces; // Already in pieces
        }
    }
}

