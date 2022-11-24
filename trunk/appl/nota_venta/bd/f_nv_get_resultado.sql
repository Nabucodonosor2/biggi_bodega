------------------f_nv_get_resultado----------------
--	ESTA FUNCION DESPLIEGA LA FECHA DE EMISION EN LOS REPORTES EN DISTINTO FORMATO--
ALTER FUNCTION [dbo].[f_nv_get_resultado](@ve_cod_nota_venta numeric, @ve_formato varchar(25))
RETURNS numeric (14,2)
AS
BEGIN

		declare @res T_PRECIO,
				@k_estado_oc_anulada numeric,
				@porc_dscto_corporativo T_PORCENTAJE

		set @k_estado_oc_anulada = 2
		
		if (@ve_formato = 'PORC_DSCTO_TOTAL')
			SELECT @res = (((MONTO_DSCTO1 + MONTO_DSCTO2)/SUBTOTAL)) *100
			FROM NOTA_VENTA 
			WHERE cod_nota_venta = @ve_cod_nota_venta
		
		else if (@ve_formato = 'MONTO_DSCTO_TOTAL')
			SELECT @res = MONTO_DSCTO1 + MONTO_DSCTO2
			FROM NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta
		
		else if (@ve_formato = 'TOTAL_NETO')
			SELECT @res = TOTAL_NETO	
			FROM NOTA_VENTA 
		WHERE cod_nota_venta = @ve_cod_nota_venta
			
		else if (@ve_formato = 'PORC_DSCTO_CORPORATIVO')
			SELECT @res = dbo.f_get_porc_dscto_corporativo_empresa(COD_EMPRESA, FECHA_NOTA_VENTA)/100
			FROM NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta
		
		else if (@ve_formato = 'MONTO_DSCTO_CORPORATIVO')begin
			SELECT @porc_dscto_corporativo = porc_dscto_corporativo 
			FROM NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta 
				
			if(@porc_dscto_corporativo = 0.00)	
				set @res = 0
			else
				SELECT @res = dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'TOTAL_NETO')* porc_dscto_corporativo / 100
				FROM NOTA_VENTA
				WHERE cod_nota_venta = @ve_cod_nota_venta 
		end
		else if (@ve_formato = 'VENTA_NETA')
			SELECT @res = (dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'TOTAL_NETO')) - 
						  (dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO'))
			FROM NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta

		else if (@ve_formato = 'MONTO_GASTO_FIJO')
			SELECT @res = (dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'TOTAL_NETO')-
						(dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')))*
						(dbo.f_get_parametro_porc('GF', FECHA_NOTA_VENTA)/100)
			FROM NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta

		else if (@ve_formato = 'VENTA_NETA_FINAL')
			SELECT @res = dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'VENTA_NETA') -
				dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') 
			FROM NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta

		else if (@ve_formato = 'SUM_OC_TOTAL') 
			SELECT @res = sum(TOTAL_NETO)
			FROM  ORDEN_COMPRA
			WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
			AND COD_ESTADO_ORDEN_COMPRA <> @k_estado_oc_anulada
			AND TIPO_ORDEN_COMPRA = 'NOTA_VENTA';

		else if (@ve_formato = 'RESULTADO')
			SELECT @res = dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'TOTAL_NETO') -
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'SUM_OC_TOTAL') - 
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') -	
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')		
			FROM  NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta 
	
		else if (@ve_formato = 'PORC_RESULTADO')
			SELECT @res = ((dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO')) /
						(dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'TOTAL_NETO') - 
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')))* 100
			FROM  NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta
 	
		else if (@ve_formato = 'MONTO_DIRECTORIO')
			SELECT @res = (dbo.f_get_parametro_porc('AA', FECHA_NOTA_VENTA)/100) * 
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO')

			FROM NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta
		
		else if (@ve_formato = 'COMISION_V1')
			SELECT @res = (PORC_VENDEDOR1/100)*
					 dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO')
			FROM  NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta 
		
		else if (@ve_formato = 'COMISION_V2')
			SELECT @res = (PORC_VENDEDOR2/100)*
						 dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO')
			FROM  NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta 
			
		else if (@ve_formato = 'COMISION_GV')
			SELECT @res = (dbo.f_get_parametro_porc('GV', FECHA_NOTA_VENTA)/100) * 
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO') 
						
			FROM  NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta 

		else if (@ve_formato = 'COMISION_ADM')
			SELECT @res = (dbo.f_get_parametro_porc('ADM', FECHA_NOTA_VENTA)/100) * 
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO') 
						
			FROM  NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta 
	
		else if (@ve_formato = 'REMANENTE')
			SELECT @res = dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO') -
						 dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DIRECTORIO') -
						 dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'COMISION_V1') - 
						 dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'COMISION_V2') -
						 dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'COMISION_GV') -
						 dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'COMISION_ADM')
			FROM  NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta

		else if (@ve_formato = 'PAGO_DIRECTORIO' or
				 @ve_formato = 'PAGO_GV' or
				 @ve_formato = 'PAGO_ADM' or
				 @ve_formato = 'PAGO_VENDEDOR') BEGIN

			declare
				@vl_cod_tipo_orden_pago		numeric

				
			if (@ve_formato = 'PAGO_DIRECTORIO')
				set @vl_cod_tipo_orden_pago = 3
			else if (@ve_formato = 'PAGO_GV')
				set @vl_cod_tipo_orden_pago = 2
			else if (@ve_formato = 'PAGO_ADM')
				set @vl_cod_tipo_orden_pago = 4
			else if (@ve_formato = 'PAGO_VENDEDOR')
				set @vl_cod_tipo_orden_pago = 1			

			
			-- pagos
			select @res = isnull(sum(dbo.f_op_pago(cod_orden_pago)), 0)
			from orden_pago
			where cod_nota_venta = @ve_cod_nota_venta 
			  and cod_tipo_orden_pago = @vl_cod_tipo_orden_pago
		END

return isnull (round(@res, 0), 0);
END