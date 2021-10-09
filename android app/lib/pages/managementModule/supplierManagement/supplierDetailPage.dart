import 'package:bkrm/services/info/inventoryInfo/supplierInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

class SupplierDetailPage extends StatefulWidget {
  SupplierInfo supplierData;

  SupplierDetailPage(this.supplierData);

  @override
  _SupplierDetailPageState createState() => _SupplierDetailPageState();
}

class _SupplierDetailPageState extends State<SupplierDetailPage> {
  final _formKey = GlobalKey<FormState>();

  bool editEnable = false;
  bool edited = false;

  TextEditingController phoneNumberController = TextEditingController();
  TextEditingController nameController = TextEditingController();
  TextEditingController addressController = TextEditingController();
  TextEditingController emailController = TextEditingController();

  bool phoneNumberValid = true;
  bool nameValid = true;
  bool addressValid = true;
  bool emailValid = true;

  bool deleted = false;

  @override
  void initState() {
    super.initState();
    setUpSupplierInfo();
  }

  setUpSupplierInfo() {
    phoneNumberController.text = widget.supplierData.phoneNumber!;
    nameController.text =
        widget.supplierData.name == null ? "" : widget.supplierData.name!;
    addressController.text =
        widget.supplierData.address == null ? "" : widget.supplierData.address!;
    emailController.text =
        widget.supplierData.email == null ? "" : widget.supplierData.email!;
  }

  clearPage() {
    phoneNumberController.clear();
    nameController.clear();
    emailController.clear();
    addressController.clear();
    emailValid = true;
    addressValid = true;
    nameValid = true;
    phoneNumberValid = true;
  }

  void remoteSetState() {
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        title: Text("Thông tin nhà cung cấp"),
      ),
      body: WillPopScope(
        onWillPop: () async {
          Navigator.pop(context, edited);
          debugPrint("Will pop work");
          return edited;
        },
        child: SingleChildScrollView(
          child: Container(
            padding: EdgeInsets.all(8.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    height: 30,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Số điện thoại: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: editEnable,
                          keyboardType: TextInputType.phone,
                          controller: phoneNumberController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (phoneNumber) {
                            if (phoneNumber == null || phoneNumber == "") {
                              phoneNumberValid = false;
                              return " *Bắt buộc";
                            } else {
                              phoneNumberValid = true;
                              return null;
                            }
                          },
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Họ tên: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: editEnable,
                          controller: nameController,
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Địa chỉ : ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: editEnable,
                          controller: addressController,
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Email : ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: editEnable,
                          controller: emailController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (email) {
                            if (email == null || email == "") {
                              emailValid = true;
                              return null;
                            }
                            if (RegExp(
                                    r"^[a-zA-Z0-9.a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]+@[a-zA-Z0-9]+\.[a-zA-Z]+")
                                .hasMatch(email)) {
                              emailValid = true;
                              return null;
                            } else {
                              emailValid = false;
                              return " * Email không hợp lệ";
                            }
                          },
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  SizedBox(
                    height: 30,
                  ),
                  Center(
                      child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: editEnable
                              ? [
                                  ElevatedButton(
                                    onPressed: () {
                                      editEnable = false;
                                      clearPage();
                                      setUpSupplierInfo();
                                      setState(() {});
                                    },
                                    child: Container(
                                      padding: EdgeInsets.all(10.0),
                                      child: Text("Hủy"),
                                    ),
                                  ),
                                  ElevatedButton(
                                    onPressed: () async {
                                      if (editEnable) {
                                        _formKey.currentState!.validate();
                                        if (phoneNumberValid && emailValid) {
                                          showDialog(
                                              context: context,
                                              builder: (context) {
                                                return AlertDialog(
                                                  content: Container(
                                                    height: 60,
                                                    child: Center(
                                                      child:
                                                          CircularProgressIndicator(),
                                                    ),
                                                  ),
                                                );
                                              });
                                          MsgInfoCode? returnCode =
                                              await BkrmService().editSupplier(
                                                  id: widget.supplierData.id,
                                                  phoneNumber:
                                                      phoneNumberController
                                                          .value.text,
                                                  name:
                                                      nameController.value.text,
                                                  address: addressController
                                                              .value.text ==
                                                          ""
                                                      ? null
                                                      : addressController
                                                          .value.text,
                                                  email: emailController
                                                              .value.text ==
                                                          ""
                                                      ? null
                                                      : emailController
                                                          .value.text,
                                                  deleted: false);
                                          Navigator.pop(context);
                                          if (returnCode ==
                                              MsgInfoCode.actionSuccess) {
                                            widget.supplierData.address=addressController.value.text;
                                            widget.supplierData.name=nameController.value.text;
                                            widget.supplierData.email=emailController.value.text;
                                            widget.supplierData.phoneNumber=phoneNumberController.value.text;
                                            showDialog(
                                                context: this.context,
                                                builder: (context) {
                                                  return AlertDialog(
                                                    title: Text(
                                                        "Thay đổi thông tin nhà cung cấp thành công"),
                                                    actions: [
                                                      FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                            editEnable = false;
                                                            edited = true;
                                                            this.remoteSetState();
                                                          },
                                                          child: Text("Đóng"))
                                                    ],
                                                  );
                                                });
                                            return;
                                          } else {
                                            if (returnCode ==
                                                MsgInfoCode
                                                    .phoneNumberAlreadyBeenTaken) {
                                              showDialog(
                                                  context: this.context,
                                                  builder: (context) {
                                                    return AlertDialog(
                                                      title: Text(
                                                          "Số điện thoại đã tồn tại. "),
                                                      actions: [
                                                        FlatButton(
                                                            onPressed: () {
                                                              Navigator.pop(
                                                                  context);
                                                            },
                                                            child: Text("Đóng"))
                                                      ],
                                                    );
                                                  });
                                              return;
                                            }
                                            if (returnCode ==
                                                MsgInfoCode.actionFail) {
                                              showDialog(
                                                  context: this.context,
                                                  builder: (context) {
                                                    return AlertDialog(
                                                      title: Text(
                                                          "Thay đổi thông tin nhà cung cấp thất bại"),
                                                      actions: [
                                                        FlatButton(
                                                            onPressed: () {
                                                              Navigator.pop(
                                                                  context);
                                                            },
                                                            child: Text("Đóng"))
                                                      ],
                                                    );
                                                  });
                                              return;
                                            }
                                            showDialog(
                                                context: this.context,
                                                builder: (context) {
                                                  return AlertDialog(
                                                    title: Text("Lỗi hệ thống"),
                                                    actions: [
                                                      FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                          },
                                                          child: Text("Đóng"))
                                                    ],
                                                  );
                                                });
                                          }
                                        }
                                      } else {
                                        editEnable = true;
                                        setState(() {});
                                      }
                                    },
                                    child: Container(
                                        padding: EdgeInsets.all(10.0),
                                        child: Text(
                                              "Xác nhận",
                                          style: TextStyle(
                                              color: Colors.white,
                                              fontSize: 16),
                                        )),
                                  ),
                                  RaisedButton(
                                    padding: EdgeInsets.all(10.0),
                                    color: Colors.red,
                                    child: Text(
                                      "Xoá",
                                      style: TextStyle(
                                          color: Colors.white, fontSize: 16),
                                    ),
                                    onPressed: () {
                                      showDialog(
                                          context: context,
                                          builder: (context) {
                                            return AlertDialog(
                                              title: Text(
                                                  "Bạn có chắc chắn muốn xoá nhà cung cấp này?"),
                                              actions: [
                                                FlatButton(
                                                    onPressed: () async {
                                                      Navigator.pop(context);
                                                      showDialog(
                                                          context: this.context,
                                                          builder: (context) {
                                                            return AlertDialog(
                                                              content:
                                                                  Container(
                                                                height: 60,
                                                                child: Center(
                                                                  child:
                                                                      CircularProgressIndicator(),
                                                                ),
                                                              ),
                                                            );
                                                          });
                                                      MsgInfoCode? returnCode = await BkrmService().editSupplier(
                                                          id: widget
                                                              .supplierData.id,
                                                          phoneNumber:
                                                              phoneNumberController
                                                                  .value.text,
                                                          name: nameController.value
                                                              .text,
                                                          address: addressController
                                                                      .value.text ==
                                                                  ""
                                                              ? null
                                                              : addressController
                                                                  .value.text,
                                                          email: emailController
                                                                      .value
                                                                      .text ==
                                                                  ""
                                                              ? null
                                                              : emailController
                                                                  .value.text,
                                                          deleted: true);
                                                      if (returnCode ==
                                                          MsgInfoCode
                                                              .actionSuccess) {
                                                        showDialog(
                                                            context:
                                                                this.context,
                                                            builder: (context) {
                                                              return AlertDialog(
                                                                title: Text(
                                                                    "Xoá nhà cung cấp thành công"),
                                                                actions: [
                                                                  FlatButton(
                                                                      onPressed:
                                                                          () {
                                                                        edited =
                                                                            true;
                                                                        Navigator.pop(
                                                                            context);
                                                                        Navigator.pop(
                                                                            context);
                                                                        Navigator.pop(
                                                                            context,
                                                                            edited);
                                                                      },
                                                                      child: Text(
                                                                          "Đóng"))
                                                                ],
                                                              );
                                                            });
                                                      } else {
                                                        showDialog(
                                                            context: context,
                                                            builder: (context) {
                                                              return AlertDialog(
                                                                title: Text(
                                                                    "Xoá nhà cung cấp thất bại"),
                                                                actions: [
                                                                  FlatButton(
                                                                      onPressed:
                                                                          () {
                                                                        Navigator.pop(
                                                                            context);
                                                                        Navigator.pop(
                                                                            context);
                                                                      },
                                                                      child: Text(
                                                                          "Đóng"))
                                                                ],
                                                              );
                                                            });
                                                      }
                                                    },
                                                    child: Text("Chắc chắn")),
                                                FlatButton(
                                                    onPressed: () {
                                                      Navigator.pop(context);
                                                    },
                                                    child: Text("Huỷ")),
                                              ],
                                            );
                                          });
                                    },
                                  )
                                ]
                              : [
                                  RaisedButton(
                                    padding: EdgeInsets.all(10.0),
                                    color: Colors.blue,
                                    onPressed: () async {
                                      if(editEnable==false){
                                        editEnable = true;
                                        setState(() {});
                                      }
                                    },
                                    child: Text(
                                      editEnable == false
                                          ? "Chỉnh sửa"
                                          : "Xác nhận",
                                      style: TextStyle(
                                          color: Colors.white, fontSize: 16),
                                    ),
                                  ),
                                  RaisedButton(
                                    padding: EdgeInsets.all(10.0),
                                    color: Colors.red,
                                    child: Text(
                                      "Xoá",
                                      style: TextStyle(
                                          color: Colors.white, fontSize: 16),
                                    ),
                                    onPressed: () {
                                      showDialog(
                                          context: context,
                                          builder: (context) {
                                            return AlertDialog(
                                              title: Text(
                                                  "Bạn có chắc chắn muốn xoá nhà cung cấp này?"),
                                              actions: [
                                                FlatButton(
                                                    onPressed: () async {
                                                      Navigator.pop(context);
                                                      showDialog(
                                                          context: this.context,
                                                          builder: (context) {
                                                            return AlertDialog(
                                                              content:
                                                                  Container(
                                                                height: 60,
                                                                child: Center(
                                                                  child:
                                                                      CircularProgressIndicator(),
                                                                ),
                                                              ),
                                                            );
                                                          });
                                                      MsgInfoCode? returnCode = await BkrmService().editSupplier(
                                                          id: widget
                                                              .supplierData.id,
                                                          phoneNumber:
                                                              phoneNumberController
                                                                  .value.text,
                                                          name: nameController.value
                                                              .text,
                                                          address: addressController
                                                                      .value.text ==
                                                                  ""
                                                              ? null
                                                              : addressController
                                                                  .value.text,
                                                          email: emailController
                                                                      .value
                                                                      .text ==
                                                                  ""
                                                              ? null
                                                              : emailController
                                                                  .value.text,
                                                          deleted: true);
                                                      if (returnCode ==
                                                          MsgInfoCode
                                                              .actionSuccess) {
                                                        showDialog(
                                                            context:
                                                                this.context,
                                                            builder: (context) {
                                                              return AlertDialog(
                                                                title: Text(
                                                                    "Xoá nhà cung cấp thành công"),
                                                                actions: [
                                                                  FlatButton(
                                                                      onPressed:
                                                                          () {
                                                                        edited =
                                                                            true;
                                                                        Navigator.pop(
                                                                            context);
                                                                        Navigator.pop(
                                                                            context);
                                                                        Navigator.pop(
                                                                            context,
                                                                            edited);
                                                                      },
                                                                      child: Text(
                                                                          "Đóng"))
                                                                ],
                                                              );
                                                            });
                                                      } else {
                                                        showDialog(
                                                            context: context,
                                                            builder: (context) {
                                                              return AlertDialog(
                                                                title: Text(
                                                                    "Xoá nhà cung cấp thất bại"),
                                                                actions: [
                                                                  FlatButton(
                                                                      onPressed:
                                                                          () {
                                                                        Navigator.pop(
                                                                            context);
                                                                        Navigator.pop(
                                                                            context);
                                                                      },
                                                                      child: Text(
                                                                          "Đóng"))
                                                                ],
                                                              );
                                                            });
                                                      }
                                                    },
                                                    child: Text("Chắc chắn")),
                                                FlatButton(
                                                    onPressed: () {
                                                      Navigator.pop(context);
                                                    },
                                                    child: Text("Huỷ")),
                                              ],
                                            );
                                          });
                                    },
                                  )
                                ]))
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
