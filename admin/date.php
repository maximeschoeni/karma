<?php

class Karma_Date {

  /**
   * timestamp to sql
   */
  static function format($timestamp, $format = 'yyyy-mm-dd hh:ii:ss', $input_format = null) {

    if ($input_format) {

      $timestamp = self::parse($timestamp, $input_format);

    }

    if ($timestamp) {

      $format = str_replace(array(
        'yyyy',
        'mm',
        'dd',
        'hh',
        'ii',
        'ss',
        '#m',
        '#d'
      ), array(
        'Y',
        'm',
        'd',
        'H',
        'i',
        's',
        'n',
        'j'
      ), $format);

      return date($format, $timestamp);

    }

    return '';
  }

  /**
   * custom format to timestamp
   */
  static function parse($string, $format = 'yyyy-mm-dd hh:ii:ss', $output_format = null) {

    $reg_exps = array(
      'yyyy'=> '([0-9]{4})',
      'mm' => '([0-9]{2})',
      'dd' => '([0-9]{2})',
      'hh' => '([0-9]{2})',
      'ii' => '([0-9]{2})',
      'ss' => '([0-9]{2})',
      '#m' => '([0-9]+)',
      '#d' => '([0-9]+)'
    );

    $time_indexes = array(
      'yyyy' => 5,
      'mm' => 3,
      'dd' => 4,
      'hh' => 0,
      'ii' => 1,
      'ss' => 2,
      '#m' => 3,
      '#d' => 4
    );

    $string_regex = '@'.str_replace(array_keys($reg_exps), array_values($reg_exps), $format).'@';
    $format_regex = '@('.implode('|', array_keys($time_indexes)).')@';

    if (preg_match_all($format_regex, $format, $format_matches) && preg_match($string_regex, $string, $string_matches)) {

      $time_values = array_fill(0, 6, 0);
      $string_matches = array_slice($string_matches, 1);

      foreach ($format_matches[0] as $i => $match) {

        $time_values[$time_indexes[$match]] = intval($string_matches[$i]);

      }

      $timestamp = mktime($time_values[0], $time_values[1], $time_values[2], $time_values[3], $time_values[4], $time_values[5]);

      if ($output_format) {

        return self::format($timestamp, $output_format);

      } else {

        return $timestamp;

      }

    }

  }

  /**
   * get date range from sql dates
   */
  static function format_range($date1, $date2) {

    $t1 = self::parse($date1);
    $t2 = self::parse($date2);

    $d1 = date('d', $t1);
    $m1 = date('m', $t1);
    $y1 = date('Y', $t1);
    $d2 = date('d', $t2);
    $m2 = date('m', $t2);
    $y2 = date('Y', $t2);

    if ($y1 === $y2) {

      if ($m1 === $m2) {

        if ($d1 === $d2) {

          return date('d.m.Y', $t2);

        } else {

          return date('d', $t1) . ' — ' . date('d.m.Y', $t2);

        }

      } else {

        return date('d.m', $t1) . ' — ' . date('d.m.Y', $t2);

      }

    } else {

      return date('d.m.Y', $t1) . ' — ' . date('d.m.Y', $t2);

    }

  }

}
