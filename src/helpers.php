<?php
/**
 * Helper functions for the Travel Booking System
 * Provides formatting and validation utilities
 */

/**
 * Format price in Vietnamese Dong (VNĐ)
 * 
 * @param float|int $price The price to format
 * @return string Formatted price string with VNĐ
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

/**
 * Format date to dd/mm/yyyy format
 * 
 * @param string $date Date string (MySQL format YYYY-MM-DD or timestamp)
 * @return string Formatted date string in dd/mm/yyyy format
 */
function formatDate($date) {
    if (empty($date)) {
        return '';
    }
    
    // Convert to timestamp if it's a date string
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    
    if ($timestamp === false) {
        return $date; // Return original if conversion fails
    }
    
    return date('d/m/Y', $timestamp);
}

/**
 * Validate booking data
 * 
 * @param int $num_people Number of people to book
 * @param int $available_seats Available seats in the tour
 * @return array Array of error messages (empty if valid)
 */
function validateBooking($num_people, $available_seats) {
    $errors = [];
    
    // Check if num_people is a valid integer
    if (!is_numeric($num_people) || $num_people != (int)$num_people) {
        $errors[] = "Số lượng người phải là số nguyên";
        return $errors;
    }
    
    $num_people = (int)$num_people;
    
    // Check if num_people is greater than 0
    if ($num_people <= 0) {
        $errors[] = "Số lượng người phải lớn hơn 0";
    }
    
    // Check if num_people doesn't exceed available seats
    if ($num_people > $available_seats) {
        $errors[] = "Không đủ chỗ. Chỉ còn $available_seats chỗ trống";
    }
    
    return $errors;
}

/**
 * Validate tour data
 * 
 * @param array $data Tour data to validate
 * @return array Array of error messages (empty if valid)
 */
function validateTourData($data) {
    $errors = [];
    
    // Check required fields
    if (empty($data['name']) || trim($data['name']) === '') {
        $errors[] = "Tên tour không được để trống";
    }
    
    if (empty($data['description']) || trim($data['description']) === '') {
        $errors[] = "Mô tả tour không được để trống";
    }
    
    // Validate price
    if (!isset($data['price']) || !is_numeric($data['price'])) {
        $errors[] = "Giá tour phải là số";
    } elseif ($data['price'] <= 0) {
        $errors[] = "Giá tour phải lớn hơn 0";
    }
    
    // Validate available_seats
    if (!isset($data['available_seats']) || !is_numeric($data['available_seats'])) {
        $errors[] = "Số chỗ phải là số nguyên";
    } elseif ($data['available_seats'] < 0) {
        $errors[] = "Số chỗ không được âm";
    }
    
    // Validate departure_date
    if (empty($data['departure_date'])) {
        $errors[] = "Ngày khởi hành không được để trống";
    } else {
        $timestamp = strtotime($data['departure_date']);
        if ($timestamp === false) {
            $errors[] = "Ngày khởi hành không hợp lệ";
        }
    }
    
    // Validate place_id
    if (!isset($data['place_id']) || !is_numeric($data['place_id'])) {
        $errors[] = "Địa điểm không hợp lệ";
    }
    
    return $errors;
}
?>
