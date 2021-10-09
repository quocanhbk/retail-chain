import 'package:flutter/services.dart';
import 'package:intl/intl.dart';

class CustomerFormatter {
  static String formatCurrency(num value, {int fractionDigits = 0}) {
    ArgumentError.checkNotNull(value, 'value');

    // convert cents into hundreds.
    return NumberFormat.currency(
      decimalDigits: 0,
      customPattern: '###,###',
      // using Netherlands because this country also
      // uses the comma for thousands and dot for decimal separators.
    ).format(value);
  }

  TextInputFormatter currencyFormatter =
      TextInputFormatter.withFunction((oldValue, newValue) {
    // remove characters to convert the value to double (because one of those may appear in the keyboard)
    String newText = newValue.text
        .replaceAll('.', '')
        .replaceAll(',', '')
        .replaceAll('_', '')
        .replaceAll('-', '')
        .replaceAll(' ', '');
    String value = newText;
    int cursorPosition = newText.length;
    if (newText.isNotEmpty) {
      value = formatCurrency(double.parse(newText), fractionDigits: 0);
      cursorPosition = value.length;
    }
    return TextEditingValue(
        text: value,
        selection: TextSelection.collapsed(offset: cursorPosition));
  });
  TextInputFormatter numberFormatter =
      TextInputFormatter.withFunction((oldValue, newValue) {
    // remove characters to convert the value to double (because one of those may appear in the keyboard)
    String newText = newValue.text
        .replaceAll('.', '')
        .replaceAll(',', '')
        .replaceAll('_', '')
        .replaceAll('-', '')
        .replaceAll(' ', '');
    String value = newText;
    int cursorPosition = newText.length;
    return TextEditingValue(
        text: value,
        selection: TextSelection.collapsed(offset: cursorPosition));
  });
}
