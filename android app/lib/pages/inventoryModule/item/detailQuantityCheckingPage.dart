import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/quantityHistory.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class QuantityCheckingDetailPage extends StatefulWidget {
  ItemQuantityChange quantityHistory;
  ItemInfo item;

  QuantityCheckingDetailPage(this.quantityHistory, this.item);

  @override
  _QuantityCheckingDetailPageState createState() =>
      _QuantityCheckingDetailPageState();
}

class _QuantityCheckingDetailPageState
    extends State<QuantityCheckingDetailPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Đơn kiểm kê hàng"),
      ),
      body: Container(
        padding: EdgeInsets.all(8.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Center(
              child: widget.item.imageUrl != null &&
                      widget.item.imageUrl != "null"
                  ? CachedNetworkImage(
                      imageUrl: ServerConfig.projectUrl + widget.item.imageUrl!,
                      height: 100,
                      width: 100,
                    )
                  : Image.asset("asset/productImage/no-image.jpg"),
            ),
            Center(
              child: Text(
                widget.item.itemName!,
                textAlign: TextAlign.center,
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
              ),
            ),
            SizedBox(
              height: 20,
            ),
            Center(
              child: Text(
                "Thông tin điều chỉnh",
                textAlign: TextAlign.center,
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
              ),
            ),
            Row(
              children: [
                Expanded(flex: 1, child: Text("Số lượng cũ : ")),
                Expanded(
                    flex: 2,
                    child: TextField(
                      controller: TextEditingController(
                          text: widget.quantityHistory.oldQuantity.toString()),
                    )),
              ],
            ),
            Row(
              children: [
                Expanded(flex: 1, child: Text("Số lượng mới : ")),
                Expanded(
                    flex: 2,
                    child: TextField(
                      controller: TextEditingController(
                          text: widget.quantityHistory.newQuantity.toString()),
                    )),
              ],
            ),
            Row(
              children: [
                Expanded(flex: 1, child: Text("Thay đổi : ")),
                Expanded(
                    flex: 2,
                    child: TextField(
                      controller: TextEditingController(
                          text: widget.quantityHistory.quantityChange),
                    )),
              ],
            ),
            Row(
              children: [
                Expanded(flex: 1, child: Text("Người thực hiện : ")),
                Expanded(
                    flex: 2,
                    child: TextField(
                      controller: TextEditingController(
                          text: widget.quantityHistory.checkerName
                              .toString()),
                    )),
              ],
            ),
            Row(
              children: [
                Expanded(flex: 1, child: Text("Lý do : ")),
                Expanded(
                    flex: 2,
                    child: TextField(
                      controller: TextEditingController(
                          text: widget.quantityHistory.reason.toString()),
                    )),
              ],
            ),
            Row(
              children: [
                Expanded(flex: 1, child: Text("Ngày thực hiện : ")),
                Expanded(
                    flex: 2,
                    child: TextField(
                      controller: TextEditingController(
                          text: DateFormat("HH:mm:ss dd-MM-yyyy").format(
                              widget.quantityHistory.createdDatetime)),
                    )),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
