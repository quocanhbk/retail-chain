import 'refundInfo.dart';

class RefundPagination{
  int currentPage;
  int lastPage;
  int refundPerPAge;
  List<RefundSheet> refunds;

  RefundPagination(
      this.currentPage, this.lastPage, this.refundPerPAge, this.refunds);
}