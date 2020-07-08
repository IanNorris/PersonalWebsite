Version *version = ...;
int writeOffset = ...;
float *ActualData1;

_List_node<Version,void*> *head = (version->Data)._Mypair._Myval2._Myhead;
_List_node<Version,void*> *node = head->_Next;
while (node != head)
{
	ActualData1 = *(float **)&(node->_Myval).Transform;
	//if ((index + writeOffset) % 100 == 0)
	if (writeOffset == ((writeOffset / 100 + (writeOffset >> 0x1f)) - (int)((longlong)writeOffset * 0x51eb851f >> 0x3f)) * 100)
	{
		(node->_Myval).Result =
			(((((node->_Myval).ActualData2[1] + ActualData1[1] +
			(0.00000000 - ((node->_Myval).ActualData2[0] + *ActualData1))) *
			((node->_Myval).ActualData2[2] + ActualData1[2] + 1.00000000)) /
			(1.00000000 - ((node->_Myval).ActualData2[3] + ActualData1[3])) -
			((node->_Myval).ActualData2[4] + ActualData1[4])) /
			((node->_Myval).ActualData2[5] + ActualData1[5] + 1.00000000)) *
			((node->_Myval).ActualData2[6] + ActualData1[6] + 1.00000000) +
			(node->_Myval).ActualData2[7] + ActualData1[7];
	}
	node = node->_Next;
	writeOffset = writeOffset + 1;
}