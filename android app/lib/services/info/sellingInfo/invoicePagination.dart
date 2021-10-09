import 'package:bkrm/services/info/invoice/invoiceReceivedWhenGet.dart';

class InvoicePagination{
  int currentPage;
  int lastPage;
  int invoicePerPAge;
  List<InvoiceReceivedWhenGet> invoices;

  InvoicePagination(
      this.currentPage, this.lastPage, this.invoicePerPAge, this.invoices);
}