class HistoryInfo{
  late int id;
  late String userName;
  late DateTime createdDateTime;
  late String action;
  late String type;

  HistoryInfo({ required String id, required this.userName, required String createdDateTime, required this.action, required this.type}){
    this.id=int.tryParse(id)??0;
    this.createdDateTime=DateTime.tryParse(createdDateTime)??DateTime.fromMicrosecondsSinceEpoch(0);
  }

  @override
  String toString() {
    return 'HistoryInfo{id: $id, userName: $userName, createdDateTime: $createdDateTime, action: $action, type: $type}';
  }
}