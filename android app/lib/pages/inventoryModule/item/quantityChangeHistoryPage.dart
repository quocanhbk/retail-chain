import 'package:bkrm/services/info/inventoryInfo/quantityHistory.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class QuantityChangeHistoryPage extends StatefulWidget {
  final QuantityHistory quantityHistory;

  QuantityChangeHistoryPage(this.quantityHistory);

  @override
  _QuantityChangeHistoryPageState createState() =>
      _QuantityChangeHistoryPageState();
}

class _QuantityChangeHistoryPageState extends State<QuantityChangeHistoryPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Lịch sử kiểm kê"),
      ),
      body: Container(
          padding: EdgeInsets.all(8.0),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.max,
              children: [
                Expanded(
                    flex: 1,
                    child: Text(
                      "Thời gian",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center,
                    )),
                Expanded(
                    flex: 1,
                    child: Text(
                      "Cũ",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center,
                    )),
                Expanded(
                    flex: 1,
                    child: Text(
                      "Mới",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center,
                    )),
                Expanded(
                    flex: 1,
                    child: Text(
                      "Thay đổi",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center,
                    )),
                Expanded(
                    flex: 1,
                    child: Text(
                      "Bởi",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center,
                    )),
              ],
            ),
            Divider(),
            Expanded(
              child: widget.quantityHistory.quantityHistory.isNotEmpty?ListView.separated(
                  itemBuilder: (context, index) {
                    return Column(children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                        children: [
                          Expanded(
                              flex: 1,
                              child: Text(DateFormat("dd-MM-yyyy").format(widget
                                  .quantityHistory
                                  .quantityHistory[index]
                                  .createdDatetime),textAlign: TextAlign.center,style: TextStyle(fontSize: 13),)),
                          Expanded(
                              flex: 1,
                              child: Text(widget.quantityHistory
                                  .quantityHistory[index].oldQuantity
                                  .toString(),textAlign: TextAlign.center,style: TextStyle(fontSize: 14),)),
                          Expanded(
                              flex: 1,
                              child: Text(widget.quantityHistory
                                  .quantityHistory[index].newQuantity
                                  .toString(),textAlign: TextAlign.center,style: TextStyle(fontSize: 14),)),
                          Expanded(
                              flex: 1,
                              child: Text(widget.quantityHistory
                                  .quantityHistory[index].quantityChange,textAlign: TextAlign.center,style: TextStyle(fontSize: 14),)),
                          Expanded(
                              flex: 1,
                              child: Text(widget.quantityHistory
                                  .quantityHistory[index].checkerName,textAlign: TextAlign.center,style: TextStyle(fontSize: 14),)),
                        ],
                      ),
                      SizedBox(height: 10,),
                      Container(
                        alignment: Alignment.centerLeft,
                        child: Text("Lý do: "+widget.quantityHistory.quantityHistory[index].reason),
                      )
                    ]);
                  },
                  separatorBuilder: (context, index) => Divider(),
                  itemCount: widget.quantityHistory.quantityHistory.length):
              Container(
                child: Center(
                  child: Text("Không có lần kiểm kê nào.",style: TextStyle(fontSize: 18,fontWeight: FontWeight.w300,color: Colors.grey),),
                ),
              ),
            ),
          ])),
    );
  }
}
