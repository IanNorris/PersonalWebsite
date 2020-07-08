void RunBenchmarkCoreLoop<struct_Version1>(Version1 *version,int writeOffset)
{
	_List_node<Version1::MyData,void*>* listHead = (version->Data)._Mypair._Myval2._Myhead;
	_List_node<Version1::MyData,void*>* currentListItem = listHead->_Next;
	while (currentListItem != listHead)
	{
		Version1::MyData& currentItem = currentListItem->_Myval;
		float* ActualData1 = *(float **)&currentItem.Transform;
		if (writeOffset == ((writeOffset / 100 + (writeOffset >> 0x1f)) - (int)((longlong)writeOffset * 0x51eb851f >> 0x3f)) * 100)
		{
			currentItem.Result =
			   ((((currentItem.ActualData2[1] + ActualData1[1] +
				  (0.00000000 - (currentItem.ActualData2[0] + *ActualData1))) *
				 (currentItem.ActualData2[2] + ActualData1[2] + 1.00000000)) /
				 (1.00000000 - (currentItem.ActualData2[3] + ActualData1[3])) -
				(currentItem.ActualData2[4] + ActualData1[4])) /
			   (currentItem.ActualData2[5] + ActualData1[5] + 1.00000000)) *
			   (currentItem.ActualData2[6] + ActualData1[6] + 1.00000000) +
			   currentItem.ActualData2[7] + ActualData1[7];
		}
		currentListItem = currentListItem->_Next;
		writeOffset = writeOffset + 1;
	}
	return;
}